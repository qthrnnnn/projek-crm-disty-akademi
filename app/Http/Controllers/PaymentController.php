<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    private const PROOF_RULES = 'file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/gif,image/bmp|max:5120';

    public function index(Request $request)
    {
        $query = Payment::with(['lead', 'product', 'verifiedByUser'])->latest();

        if (auth()->user()->isSales()) {
            $query->whereHas('lead', function ($q) {
                $q->where('assigned_to', auth()->id());
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $query = Lead::awaitingPaymentSubmission()->orderBy('name');
        if (auth()->user()->isSales()) {
            $query->where('assigned_to', auth()->id());
        }
        $leads = $query->get();
        $products = Product::active()->get();

        return view('payments.create', compact('leads', 'products'));
    }

    public function store(Request $request)
    {
        $requiresProof = $request->user()?->isSales();
        $data = $request->validate([
            'lead_id' => 'required_without:registration_id|nullable|integer|exists:leads,id',
            'registration_id' => 'nullable|integer',
            'product_id' => 'nullable|integer|exists:products,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'proof_file' => ($requiresProof ? 'required_without:proof|' : '').'nullable|'.self::PROOF_RULES,
            'proof' => ($requiresProof ? 'required_without:proof_file|' : '').'nullable|'.self::PROOF_RULES,
        ]);

        unset($data['proof_file'], $data['proof']);
        $proof = $request->file('proof_file') ?? $request->file('proof');
        $storedProof = null;
        $user = $request->user();

        try {
            $payment = DB::transaction(function () use ($data, $proof, $user, &$storedProof) {
                $registration = null;
                if (isset($data['registration_id'])) {
                    $registration = Registration::query()
                        ->when($user->isSales(), fn ($query) => $query->handledBy($user->id))
                        ->lockForUpdate()
                        ->findOrFail($data['registration_id']);
                    if ($registration->status !== 'registered') {
                        throw ValidationException::withMessages(['registration_id' => 'Pendaftaran ini sudah tidak menunggu pembayaran.']);
                    }

                    $customer = $registration->customer()->lockForUpdate()->firstOrFail();
                    $lead = $customer->lead()->lockForUpdate()->first();
                    if (! $lead) {
                        $lead = Lead::create([
                            'lead_code' => Lead::generateCode(),
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'product_id' => $registration->product_id,
                            'assigned_to' => $registration->user_id ?? $user->id,
                            'status' => 'negotiation',
                        ]);
                        $customer->update(['lead_id' => $lead->id]);
                    } elseif ($user->isSales() && $lead->assigned_to === null) {
                        $lead->update(['assigned_to' => $user->id, 'assigned_at' => now()]);
                    }

                    $data['product_id'] = $registration->product_id;
                } else {
                    $lead = Lead::query()->lockForUpdate()->findOrFail($data['lead_id']);
                    $data['product_id'] ??= $lead->product_id;
                }

                abort_if(
                    $user->isSales() && $lead->assigned_to !== $user->id,
                    403,
                    'Anda hanya dapat mencatat pembayaran untuk peserta yang ditugaskan kepada Anda.',
                );

                $data['lead_id'] = $lead->id;
                if ($proof) {
                    $data['proof_file'] = $storedProof = $this->storeProof($proof);
                }

                $pending = Payment::where('payments.lead_id', $lead->id)
                    ->where('payments.product_id', $data['product_id'])
                    ->where('payments.status', 'pending');
                $payment = (clone $pending)->where('registration_id', $registration?->id)->latest('id')->first();
                if (! $payment && $registration) {
                    $payment = $pending->select('payments.*')
                        ->join('registrations', fn ($join) => $join->where('registrations.id', $registration->id))
                        ->matchingLegacyRegistration()
                        ->latest('payments.id')
                        ->first();
                }

                if ($payment) {
                    $payment->update($data);
                } else {
                    $payment = Payment::create($data);
                }

                Commission::ensureCommissionCreated($lead, (float) $data['amount'], $payment->id);

                return $payment;
            });
        } catch (\Throwable $exception) {
            if ($storedProof) {
                Storage::disk('public')->delete($storedProof);
            }
            throw $exception;
        }

        $message = $payment->proof_file
            ? 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi.'
            : 'Pembayaran berhasil dicatat. Struk dapat dilampirkan dari detail transaksi.';

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'data' => $payment->load(['lead', 'product']),
            ], 201);
        }

        return redirect()->to('/payments')->with('success', $message);
    }

    public function attachProof(Request $request, Payment $payment): JsonResponse
    {
        $request->validate(['proof_file' => 'required|'.self::PROOF_RULES]);

        $previousPath = $payment->proof_file;
        $disk = Storage::disk('public');
        abort_if($previousPath && $disk->exists($previousPath), 409, 'Transaksi ini sudah memiliki lampiran struk.');

        $path = $this->storeProof($request->file('proof_file'));
        $attached = 0;
        try {
            $attached = Payment::whereKey($payment->id)
                ->where('proof_file', $previousPath)
                ->updateWithHistory(['proof_file' => $path]);
        } finally {
            if (! $attached) {
                $disk->delete($path);
            }
        }
        abort_unless($attached, 409, 'Lampiran struk baru saja berubah. Muat ulang detail transaksi.');

        return response()->json([
            'message' => 'Struk berhasil dilampirkan.',
            'data' => $payment->refresh(),
        ]);
    }

    private function storeProof(UploadedFile $file): string
    {
        $path = $file->store('payments', 'public');
        if ($path === false) {
            throw ValidationException::withMessages(['proof_file' => 'Struk gagal disimpan. Coba unggah kembali.']);
        }

        return $path;
    }

    public function show(Payment $payment)
    {
        $payment->load(['lead', 'product', 'verifiedByUser', 'commission.user']);

        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json(['message' => 'Data pembayaran dihapus.'], 200);
        }

        return redirect()->to('/payments')->with('success', 'Data pembayaran dihapus.');
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:valid,invalid,recheck',
            'verification_notes' => 'nullable|string',
        ]);

        $operatorId = auth()->id() ?: 1;
        $payment->processVerification($request->status, $operatorId, $request->verification_notes);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'message' => 'Status pembayaran diverifikasi. Status pendaftaran peserta otomatis diubah ke Dikonfirmasi (Confirmed).',
                'data' => $payment->fresh(['lead', 'product']),
            ], 200);
        }

        return back()->with('success', 'Status pembayaran diverifikasi. Status pendaftaran peserta otomatis diubah ke Dikonfirmasi (Confirmed).');
    }
}
