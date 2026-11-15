<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
        $query = Lead::whereNotNull('product_id')
            ->whereNotIn('status', ['lost', 'inactive'])
            ->whereDoesntHave('payments', function ($p) {
                $p->where('status', 'valid');
            })
            ->orderBy('name');
        if (auth()->user()->isSales()) {
            $query->where('assigned_to', auth()->id());
        }
        $leads = $query->get();
        $products = Product::active()->get();

        return view('payments.create', compact('leads', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'product_id' => 'nullable|exists:products,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'proof_file' => 'nullable|file|max:5120',
            'proof' => 'nullable|file|max:5120',
        ]);

        $lead = Lead::findOrFail($data['lead_id']);
        if (auth()->user()->isSales() && $lead->assigned_to !== auth()->id()) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Akses ditolak: Anda hanya dapat mencatat pembayaran untuk lead yang ditugaskan kepada Anda.'], 403);
            }
            return back()->with('error', 'Akses ditolak: Anda hanya dapat mencatat pembayaran untuk lead yang ditugaskan kepada Anda.');
        }

        if ($request->hasFile('proof_file')) {
            $data['proof_file'] = $request->file('proof_file')->store('payments', 'public');
        } elseif ($request->hasFile('proof')) {
            $data['proof_file'] = $request->file('proof')->store('payments', 'public');
        }
        unset($data['proof']);

        // Cegah dobel data: jika lead sudah memiliki pembayaran berstatus pending, perbarui data tersebut
        $existingPayment = Payment::where('lead_id', $data['lead_id'])
            ->where('status', 'pending')
            ->first();

        if ($existingPayment) {
            $existingPayment->update($data);
            $payment = $existingPayment;
        } else {
            $payment = Payment::create($data);
        }

        // Otomatis buat komisi sales 10% (status pending / perlu persetujuan admin)
        Commission::ensureCommissionCreated($lead, (float) $data['amount'], $payment->id);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'message' => 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi.',
                'data' => $payment->load(['lead', 'product']),
            ], 201);
        }

        return redirect()->to('/payments')->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi.');
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
