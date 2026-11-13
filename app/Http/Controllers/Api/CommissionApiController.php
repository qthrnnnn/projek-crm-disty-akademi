<?php

namespace App\Http\Controllers\Api;

use App\Models\Commission;
use App\Models\SalesReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionApiController extends BaseApiController
{
    /**
     * Dapatkan daftar komisi sales beserta total pending dan paid
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Commission::with(['user:id,name,email', 'lead:id,lead_code,name', 'payment:id,amount,payment_method,payment_date'])->latest();

        if ($user->isSales()) {
            $query->where('user_id', $user->id);
            $totalPending = Commission::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->sum('commission_amount');
            $totalPaid = Commission::where('user_id', $user->id)->where('status', 'paid')->sum('commission_amount');
        } else {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            $totalPending = Commission::whereIn('status', ['pending', 'approved'])->sum('commission_amount');
            $totalPaid = Commission::where('status', 'paid')->sum('commission_amount');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $commissions = $query->paginate($perPage);

        return $this->sendSuccess([
            'commissions' => $commissions,
            'summary' => [
                'total_pending' => (float) $totalPending,
                'total_paid' => (float) $totalPaid,
            ],
        ], 'Data komisi berhasil diambil.');
    }

    /**
     * Admin menyetujui komisi sales (Admin Only)
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang berwenang menyetujui komisi.', 403);
        }

        $commission = Commission::find($id);
        if (! $commission) {
            return $this->sendError('Komisi tidak ditemukan.', 404);
        }

        $commission->update(['status' => 'approved', 'approved_at' => now()]);

        return $this->sendSuccess($commission->fresh(), 'Komisi berhasil disetujui.');
    }

    /**
     * Admin mencairkan/membayarkan komisi ke sales (Admin Only)
     */
    public function markPaid(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang berwenang memproses pembayaran komisi.', 403);
        }

        $commission = Commission::with('lead')->find($id);
        if (! $commission) {
            return $this->sendError('Komisi tidak ditemukan.', 404);
        }

        $commission->update(['status' => 'paid', 'paid_at' => now()]);

        SalesReminder::create([
            'type' => 'commission_paid',
            'lead_id' => $commission->lead_id,
            'commission_id' => $commission->id,
            'admin_id' => $user->id,
            'sales_id' => $commission->user_id,
            'message' => 'Selamat! Komisi 10% sebesar Rp ' . number_format($commission->commission_amount, 0, ',', '.') . ' untuk prospek ' . ($commission->lead?->name ?? 'Pelanggan') . ' telah berhasil dibayarkan.',
            'priority' => 'normal',
            'is_read' => false,
            'is_completed' => true,
        ]);

        return $this->sendSuccess($commission->fresh(), 'Komisi ditandai telah dibayarkan ke sales.');
    }
}
