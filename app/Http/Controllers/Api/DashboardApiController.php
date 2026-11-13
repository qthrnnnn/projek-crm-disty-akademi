<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    /**
     * Dapatkan ringkasan metrik dashboard, statistik lead, & KPI sales/admin
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isSales = $user ? $user->isSales() : false;
        $userId = $user ? $user->id : null;

        $leadQuery = Lead::query();
        if ($isSales && $userId) {
            $leadQuery->where('assigned_to', $userId);
        }

        $totalLeads = (clone $leadQuery)->count();
        $newLeads = (clone $leadQuery)->where('status', 'new')->count();
        $contactedLeads = (clone $leadQuery)->where('status', 'contacted')->count();
        $activeLeads = (clone $leadQuery)->where('status', 'active')->count();
        $negotiationLeads = (clone $leadQuery)->where('status', 'negotiation')->count();
        $wonLeads = (clone $leadQuery)->where('status', 'won')->count();
        $lostLeads = (clone $leadQuery)->where('status', 'lost')->count();

        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        // Follow-ups
        $fuQuery = FollowUp::where('status', 'pending');
        if ($isSales && $userId) {
            $fuQuery->where('assigned_to', $userId);
        }
        $pendingFollowUps = (clone $fuQuery)->count();
        $overdueFollowUps = (clone $fuQuery)->where('follow_up_at', '<', now())->count();

        // Finansial: Pembayaran & Komisi
        $paymentQuery = Payment::query();
        if ($isSales && $userId) {
            $paymentQuery->whereHas('lead', fn ($q) => $q->where('assigned_to', $userId));
        }
        $totalRevenue = (clone $paymentQuery)->where('status', 'verified')->sum('amount');
        $pendingRevenue = (clone $paymentQuery)->where('status', 'pending')->sum('amount');

        $commissionData = null;
        if ($isSales && $userId) {
            $commissionData = [
                'pending' => (float) Commission::where('user_id', $userId)->where('status', 'pending')->sum('amount'),
                'approved' => (float) Commission::where('user_id', $userId)->where('status', 'approved')->sum('amount'),
                'paid' => (float) Commission::where('user_id', $userId)->where('status', 'paid')->sum('amount'),
            ];
        }

        // Recent Activities
        $actQuery = Activity::with(['lead:id,name,lead_code', 'user:id,name'])->latest('activity_at');
        if ($isSales && $userId) {
            $actQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('lead', fn ($lead) => $lead->where('assigned_to', $userId));
            });
        }
        $recentActivities = $actQuery->limit(5)->get()->map(function ($act) {
            return [
                'id' => $act->id,
                'type' => $act->type,
                'subject' => $act->subject,
                'description' => $act->description,
                'activity_at' => $act->activity_at,
                'lead' => $act->lead,
                'user' => $act->user,
            ];
        });

        return $this->sendSuccess([
            'summary' => [
                'total_leads' => $totalLeads,
                'conversion_rate_percentage' => $conversionRate,
                'pending_follow_ups' => $pendingFollowUps,
                'overdue_follow_ups' => $overdueFollowUps,
                'total_verified_revenue' => (float) $totalRevenue,
                'pending_verification_revenue' => (float) $pendingRevenue,
            ],
            'leads_by_status' => [
                'new' => $newLeads,
                'contacted' => $contactedLeads,
                'active' => $activeLeads,
                'negotiation' => $negotiationLeads,
                'won' => $wonLeads,
                'lost' => $lostLeads,
            ],
            'commissions' => $commissionData,
            'recent_activities' => $recentActivities,
        ], 'Data ringkasan dashboard berhasil dimuat.');
    }
}
