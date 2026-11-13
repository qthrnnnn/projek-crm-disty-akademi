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
        $query = Lead::whereNotIn('status', ['lost', 'inactive'])->orderBy('name');
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
            'proof_file' => 'nullable|file|image|max:5120',
        ]);

        $lead = Lead::findOrFail($data['lead_id']);
        if (auth()->user()->isSales() && $lead->assigned_to !== auth()->id()) {
            return back()->with('error', 'Akses ditolak: Anda hanya dapat mencatat pembayaran untuk lead yang ditugaskan kepada Anda.');
        }

        if ($request->hasFile('proof_file')) {
            $data['proof_file'] = $request->file('proof_file')->store('payments', 'public');
        }

        Payment::create($data);

        return redirect()->route('payments.index')->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['lead', 'product', 'verifiedByUser', 'commission.user']);

        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Data pembayaran dihapus.');
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:valid,invalid,recheck',
            'verification_notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => $request->status,
            'verification_notes' => $request->verification_notes,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Jika valid dan belum ada komisi -> buat komisi otomatis 10%
        if ($request->status === 'valid' && ! $payment->commission) {
            $rate = 10.00; // Komisi 10% sesuai ketentuan evaluasi
            $salesUserId = $payment->lead->assigned_to ?: auth()->id();
            $commissionAmount = $payment->amount * ($rate / 100);

            Commission::create([
                'user_id' => $salesUserId,
                'lead_id' => $payment->lead_id,
                'payment_id' => $payment->id,
                'deal_amount' => $payment->amount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);

            // Update lead ke won
            $payment->lead->update(['status' => 'won', 'converted_at' => now()]);

            // Otomatis selesaikan follow-up & pengingat pending untuk lead ini
            $payment->lead->followUps()->where('status', 'pending')->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result_notes' => 'Otomatis selesai: Pembayaran lunas & lead closing',
            ]);
            \App\Models\SalesReminder::where('lead_id', $payment->lead_id)
                ->where('type', 'follow_up')
                ->where('is_completed', false)
                ->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                    'sales_reply' => 'Selesai: Lead closing / deal won',
                ]);

            // Log activity
            \App\Models\Activity::create([
                'lead_id' => $payment->lead_id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'subject' => 'Pembayaran Valid - Status diubah ke Berhasil (Won)',
                'description' => 'Pembayaran Rp '.number_format($payment->amount, 0, ',', '.').' terverifikasi. Komisi sales 10% (Rp '.number_format($commissionAmount, 0, ',', '.').') telah dicatat.',
                'activity_at' => now(),
            ]);

            // Otomatis masukkan ke Daftar Customer
            app(LeadController::class)->ensureCustomerCreated($payment->lead);
        }

        return back()->with('success', 'Status pembayaran diverifikasi. Status lead diubah ke Berhasil (Won), komisi 10% dicatat, dan data customer otomatis dibuat.');
    }
}
