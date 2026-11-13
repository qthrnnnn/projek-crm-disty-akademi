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

        $commission->update(['status' => 'approved', 'approved_at' => now()]);

        return back()->with('success', 'Komisi berhasil disetujui.');
    }

    public function markPaid(Commission $commission)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang berwenang memproses pembayaran komisi.');
        }

        $commission->update(['status' => 'paid', 'paid_at' => now()]);

        // Buat notifikasi pencairan komisi untuk Sales
        SalesReminder::create([
            'type' => 'commission_paid',
            'lead_id' => $commission->lead_id,
            'commission_id' => $commission->id,
            'admin_id' => auth()->id(),
            'sales_id' => $commission->user_id,
            'message' => 'Selamat! Komisi 10% sebesar Rp ' . number_format($commission->commission_amount, 0, ',', '.') . ' untuk prospek ' . ($commission->lead?->name ?? 'Pelanggan') . ' telah berhasil dibayarkan.',
            'priority' => 'normal',
            'is_read' => false,
            'is_completed' => true,
        ]);

        return back()->with('success', 'Komisi ditandai sudah dibayar dan notifikasi telah dikirim ke Sales.');
    }
}
