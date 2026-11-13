<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\LeadController;
use App\Models\Activity;
use App\Models\Commission;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\SalesReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends BaseApiController
{
    /**
     * Dapatkan daftar pembayaran
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Payment::with(['lead:id,lead_code,name,assigned_to', 'product:id,name,price', 'verifiedByUser:id,name'])->latest();

        if ($user->isSales()) {
            $query->whereHas('lead', fn ($q) => $q->where('assigned_to', $user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $payments = $query->paginate($perPage);

        return $this->sendSuccess($payments, 'Daftar pembayaran berhasil dimuat.');
    }

    /**
     * Submit bukti pembayaran baru (Sales / Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'product_id' => 'nullable|exists:products,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'proof_file' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($data['lead_id']);

        if ($user->isSales() && $lead->assigned_to && $lead->assigned_to !== $user->id) {
            return $this->sendError('Anda hanya boleh menginput pembayaran untuk lead Anda.', 403);
        }

        if ($request->hasFile('proof_file')) {
            $data['proof_file'] = $request->file('proof_file')->store('payment_proofs', 'public');
        }

        $data['status'] = 'pending';

        $payment = Payment::create($data);
        $payment->load(['lead', 'product']);

        return $this->sendSuccess($payment, 'Data pembayaran berhasil disimpan dan menunggu verifikasi Admin.', 201);
    }

    /**
     * Detail pembayaran
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $payment = Payment::with(['lead.assignedUser', 'product', 'verifiedByUser', 'commission'])->find($id);

        if (! $payment) {
            return $this->sendError('Data pembayaran tidak ditemukan.', 404);
        }

        if ($user->isSales() && $payment->lead->assigned_to !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        return $this->sendSuccess($payment, 'Detail pembayaran berhasil dimuat.');
    }

    /**
     * Verifikasi Pembayaran (Admin Only) -> Otomatis Buat Komisi 10% & Won Lead
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat memverifikasi pembayaran.', 403);
        }

        $payment = Payment::with(['lead', 'commission'])->find($id);
        if (! $payment) {
            return $this->sendError('Pembayaran tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:valid,invalid,recheck',
            'verification_notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => $validated['status'],
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        if ($validated['status'] === 'valid' && ! $payment->commission) {
            $rate = 10.00; // Komisi 10%
            $salesUserId = $payment->lead->assigned_to ?: $user->id;
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

            // Selesaikan follow-up dan reminder yang masih pending
            $payment->lead->followUps()->where('status', 'pending')->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result_notes' => 'Otomatis selesai: Pembayaran valid & closing',
            ]);

            SalesReminder::where('lead_id', $payment->lead_id)
                ->where('type', 'follow_up')
                ->where('is_completed', false)
                ->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                    'sales_reply' => 'Selesai: Lead closing / deal won',
                ]);

            Activity::create([
                'lead_id' => $payment->lead_id,
                'user_id' => $user->id,
                'type' => 'status_change',
                'subject' => 'Pembayaran Valid - Status diubah ke Berhasil (Won)',
                'description' => 'Pembayaran Rp '.number_format($payment->amount, 0, ',', '.').' terverifikasi. Komisi sales 10% telah dicatat.',
                'activity_at' => now(),
            ]);

            app(LeadController::class)->ensureCustomerCreated($payment->lead);
        }

        return $this->sendSuccess($payment->fresh(['lead', 'commission']), 'Status pembayaran berhasil diverifikasi.');
    }
}
