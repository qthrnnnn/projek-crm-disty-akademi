<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\SalesReminder;
use App\Models\User;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Commission::with(['user', 'lead', 'payment'])->latest();

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

        $commissions = $query->paginate(20)->withQueryString();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('commissions.index', compact('commissions', 'salesUsers', 'totalPending', 'totalPaid'));
    }

    public function approve(Commission $commission)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang berwenang menyetujui komisi.');
        }

        $commission->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Otomatis sinkronkan status pembayaran transaksi customer menjadi valid jika masih pending
        $payment = $commission->payment ?: $commission->lead?->payments()->where('status', 'pending')->first();
        if ($payment && $payment->status === 'pending') {
            $payment->update([
                'status' => 'valid',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => 'Otomatis diverifikasi valid saat komisi disetujui Admin',
            ]);
        }

        return back()->with('success', 'Komisi berhasil disetujui dan siap ditransfer.');
    }

    public function markPaid($requestOrCommission, $commission = null)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang berwenang memproses pembayaran komisi.');
        }

        if ($requestOrCommission instanceof Commission) {
            $commission = $requestOrCommission;
            $request = request();
        } else {
            $request = $requestOrCommission instanceof Request ? $requestOrCommission : request();
            $commission = $commission ?: $request->route('commission');
        }

        if (! $commission) {
            abort(404, 'Data komisi tidak ditemukan.');
        }

        $request->validate(['paid_at' => ['nullable', 'date']]);
        $paidAtInput = $request->input('paid_at');
        // The transfer form uses WIB; store the instant in the application's UTC timezone.
        $paidAt = $paidAtInput
            ? \Carbon\Carbon::parse($paidAtInput, 'Asia/Jakarta')->setTimezone(config('app.timezone'))
            : now();
        $notes = $request->input('notes');

        $updates = [
            'status' => 'paid',
            'paid_at' => $paidAt,
        ];

        // Kunci snapshot rekening tujuan transfer secara permanen pada saat pencairan
        if (! $commission->bank_account_number) {
            $salesUser = $commission->user;
            if ($salesUser) {
                $updates['bank_name'] = $salesUser->bank_name;
                $updates['bank_account_number'] = $salesUser->bank_account_number;
                $updates['bank_account_name'] = $salesUser->bank_account_name ?: $salesUser->name;
            }
        }

        if ($notes !== null) {
            $updates['notes'] = $notes;
        }

        $commission->update($updates);

        // Pastikan pembayaran transaksi customer juga berstatus valid
        $payment = $commission->payment ?: $commission->lead?->payments()->where('status', 'pending')->first();
        if ($payment && $payment->status === 'pending') {
            $payment->update([
                'status' => 'valid',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => 'Otomatis diverifikasi valid saat komisi dicairkan oleh Admin',
            ]);
        }

        $transferTimeFormatted = $paidAt->copy()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB';

        // Buat atau perbarui notifikasi pencairan komisi untuk Sales (1 komisi = tepat 1 notifikasi)
        SalesReminder::updateOrCreate(
            [
                'type' => 'commission_paid',
                'commission_id' => $commission->id,
            ],
            [
                'lead_id' => $commission->lead_id,
                'admin_id' => auth()->id(),
                'sales_id' => $commission->user_id,
                'message' => 'Selamat! Komisi 10% sebesar Rp ' . number_format($commission->commission_amount, 0, ',', '.') . ' untuk prospek ' . ($commission->lead?->name ?? 'Pelanggan') . ' telah berhasil ditransfer pada ' . $transferTimeFormatted . ($notes ? ' (Ref Mutasi: ' . $notes . ')' : '') . '.',
                'priority' => 'normal',
                'is_read' => false,
                'is_completed' => true,
            ]
        );

        return back()->with('success', 'Komisi berhasil ditandai sudah ditransfer pada ' . $transferTimeFormatted . ' dan notifikasi telah dikirim ke Sales.');
    }
}
