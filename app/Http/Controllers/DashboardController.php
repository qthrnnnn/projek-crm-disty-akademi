<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $selectedDate = CarbonImmutable::createFromFormat(
            'Y-m-d',
            $validated['date'] ?? today()->toDateString()
        )->startOfDay();
        $selectedDateString = $selectedDate->toDateString();

        /** @var User|null $user */
        $user = $request->user() ?: auth()->user();
        $isSales = $user ? $user->isSales() : false;
        $userId = $user ? $user->id : null;

        // Base Query: Jika Sales, hanya tampilkan data miliknya
        $leadQuery = Lead::query();
        if ($isSales && $userId) {
            $leadQuery->where('assigned_to', $userId);
        }

        $now = now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Nama Bulan Bahasa Indonesia
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $indonesianMonthsShort = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $todayDisplay = $selectedDate->format('d') . ' ' . $indonesianMonthsShort[(int) $selectedDate->format('n')] . ' ' . $selectedDate->format('Y');
        $todayIso = $selectedDateString;
        $currentYear = (int) $selectedDate->format('Y');
        $currentMonth = (int) $selectedDate->format('n');

        // Mengambil semua data prospek untuk fleksibilitas filter bulan & tahun
        $allPipelineLeads = (clone $leadQuery)
            ->with(['product', 'assignedUser', 'rejectionReason'])
            ->latest('created_at')
            ->get();

        $pipelineLeadsData = $allPipelineLeads->map(function ($lead) {
            $rawPhone = $lead->whatsapp ?: $lead->phone;
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $rawPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }

            return [
                'id' => $lead->id,
                'code' => $lead->lead_code,
                'name' => $lead->name,
                'status' => $lead->status,
                'priority' => $lead->priority ?? 'normal',
                'product' => $lead->product?->name ?? 'Program Pelatihan',
                'phone' => $lead->phone ?: ($lead->whatsapp ?: '-'),
                'whatsapp' => $lead->whatsapp ?: ($lead->phone ?: ''),
                'wa_link' => !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text=" . rawurlencode("Halo Kak {$lead->name}, salam kenal dari Disty Akademi. Ada yang bisa kami bantu terkait program pelatihan?") : null,
                'show_url' => url('/leads/' . $lead->id),
                'sales_name' => $lead->assignedUser?->name ?? 'Tim Sales',
                'created_date' => $lead->created_at->translatedFormat('d M Y'),
                'year' => (int) $lead->created_at->format('Y'),
                'month' => (int) $lead->created_at->format('n'),
                'date_iso' => $lead->created_at->format('Y-m-d'),
            ];
        })->values();

        // Default awal saat buka: Hari Ini
        $todayLeads = $allPipelineLeads->filter(fn ($l) => $l->created_at->format('Y-m-d') === $selectedDateString);
        $totalLeads = $todayLeads->count();
        $newLeads = (int) $todayLeads->where('status', 'new')->count();
        $contactedLeads = (int) $todayLeads->where('status', 'contacted')->count();
        $activeLeads = (int) $todayLeads->where('status', 'active')->count();
        $negotiationLeads = (int) $todayLeads->where('status', 'negotiation')->count();
        $wonLeads = (int) $todayLeads->where('status', 'won')->count();
        $lostLeads = (int) $todayLeads->where('status', 'lost')->count();
        $inactiveLeads = (int) $todayLeads->where('status', 'inactive')->count();
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        // Metrik Hari Ini / Tanggal Dipilih (4 Kotak Lead di Atas)
        $dateLeadQuery = (clone $leadQuery)->whereDate('leads.created_at', $selectedDateString);
        $todayLeadsIn = (clone $dateLeadQuery)->count();
        $todayLeadsUnanswered = (clone $dateLeadQuery)
            ->where('status', 'new')
            ->whereNull('first_contacted_at')
            ->count();
        $todayLeadsAnswered = max(0, $todayLeadsIn - $todayLeadsUnanswered);
        $todayLeadsResponded = (clone $dateLeadQuery)
            ->whereIn('status', ['active', 'negotiation', 'won'])->count();

        // Follow-up (Hanya lead aktif yang belum closing/won/lost)
        $fuQuery = FollowUp::pending()
            ->whereHas('lead', fn ($q) => $q->whereNotIn('status', ['won', 'lost', 'inactive']));
        if ($isSales && $userId) {
            $fuQuery->where('assigned_to', $userId);
        }
        $todayFollowUps = (clone $fuQuery)->whereDate('follow_up_at', $selectedDateString)->count();
        $overdueFollowUps = (clone $fuQuery)
            ->whereDate('follow_up_at', $selectedDateString)
            ->where('follow_up_at', '<', now())
            ->count();

        // Aktivitas Terbaru
        $actQuery = Activity::with(['lead', 'user'])->latest('activity_at');
        if ($isSales && $userId) {
            $actQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('lead', fn ($lead) => $lead->where('assigned_to', $userId));
            });
        }
        $actQuery->whereDate('activity_at', $selectedDateString);
        $recentActivities = $actQuery->limit(8)->get();

        // Follow-up Mendatang (Hanya lead aktif yang belum closing/won/lost)
        $upcomingFuQuery = FollowUp::with(['lead', 'assignedUser'])
            ->pending()
            ->whereHas('lead', fn ($q) => $q->whereNotIn('status', ['won', 'lost', 'inactive']))
            ->whereDate('follow_up_at', $selectedDateString)
            ->orderBy('follow_up_at');
        if ($isSales && $userId) {
            $upcomingFuQuery->where('assigned_to', $userId);
        }
        $upcomingFollowUps = $upcomingFuQuery->limit(6)->get();

        // Alasan Penolakan
        $rejectQuery = (clone $leadQuery)->where('status', 'lost')
            ->whereNotNull('rejection_reason_id')
            ->join('rejection_reasons', 'leads.rejection_reason_id', '=', 'rejection_reasons.id')
            ->selectRaw('rejection_reasons.label, COUNT(*) as total')
            ->groupBy('rejection_reasons.label')
            ->orderByDesc('total');
        $rejectionBreakdown = $rejectQuery->limit(6)->pluck('total', 'label');

        // Performa Sales (Hanya relevan/lengkap untuk Admin)
        $salesPerformance = User::where('role', 'sales')->where('is_active', true)
            ->withCount([
                'assignedLeads' => fn ($q) => $q->whereDate('created_at', $selectedDateString),
                'assignedLeads as won_leads_count' => fn ($q) => $q
                    ->whereDate('created_at', $selectedDateString)
                    ->where('status', 'won'),
            ])
            ->withSum(['commissions as total_commission' => fn ($q) => $q
                ->whereDate('commissions.created_at', $selectedDateString)
                ->where('status', 'paid')], 'commission_amount')
            ->orderByDesc('won_leads_count')->limit(5)->get();

        // Komisi
        $commQuery = Commission::query();
        if ($isSales && $userId) {
            $commQuery->where('user_id', $userId);
        }
        $commQuery->whereDate('created_at', $selectedDateString);
        $totalCommissionPending = (clone $commQuery)->whereIn('status', ['pending', 'approved'])->sum('commission_amount');
        $totalCommissionPaid = (clone $commQuery)->where('status', 'paid')->sum('commission_amount');

        // Antrian Pembayaran
        $payQuery = Payment::query();
        if ($isSales && $userId) {
            $payQuery->whereHas('lead', fn ($lead) => $lead->where('assigned_to', $userId));
        }
        $payQuery->whereDate('created_at', $selectedDateString);
        $pendingPayments = (clone $payQuery)->where('status', 'pending')->count();
        $recheckPayments = (clone $payQuery)->where('status', 'recheck')->count();

        // Conversion Rate
        $conversionRate = $totalLeads > 0
            ? round(($wonLeads / $totalLeads) * 100, 1)
            : 0;

        // Chart 6 Bulan
        $sixMonthLeads = collect(range(5, 0))->map(function (int $ago) use ($isSales, $userId, $selectedDate) {
            $start = $selectedDate->subMonths($ago)->startOfMonth();
            $end = $start->endOfMonth();

            $q = Lead::whereBetween('created_at', [$start, $end]);
            if ($isSales && $userId) {
                $q->where('assigned_to', $userId);
            }

            return [
                'label' => $start->translatedFormat('M Y'),
                'total' => (clone $q)->count(),
                'won' => (clone $q)->where('status', 'won')->count(),
            ];
        });

        // Bulan Ini
        $thisMonthStart = $selectedDate->startOfMonth();
        $thisMonthEnd = $selectedDate->endOfMonth();
        $monthLeadQuery = Lead::query()->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd]);
        if ($isSales && $userId) {
            $monthLeadQuery->where('assigned_to', $userId);
        }
        $newLeadsThisMonth = (clone $monthLeadQuery)->count();
        $wonLeadsThisMonth = (clone $monthLeadQuery)->where('status', 'won')
            ->whereBetween('converted_at', [$thisMonthStart, $thisMonthEnd])->count();

        return response()->json(compact(
            'isSales',
            'totalLeads', 'newLeads', 'contactedLeads', 'activeLeads',
            'negotiationLeads', 'wonLeads', 'lostLeads', 'inactiveLeads',
            'todayLeadsIn', 'todayLeadsAnswered', 'todayLeadsUnanswered', 'todayLeadsResponded',
            'todayFollowUps', 'overdueFollowUps',
            'recentActivities', 'upcomingFollowUps',
            'rejectionBreakdown', 'salesPerformance',
            'totalCommissionPending', 'totalCommissionPaid',
            'pendingPayments', 'recheckPayments',
            'conversionRate', 'sixMonthLeads',
            'newLeadsThisMonth', 'wonLeadsThisMonth',
            'selectedDate', 'selectedDateString', 'pipelineLeadsData',
            'todayIso', 'todayDisplay', 'currentYear', 'currentMonth'
        ));
    }
}
