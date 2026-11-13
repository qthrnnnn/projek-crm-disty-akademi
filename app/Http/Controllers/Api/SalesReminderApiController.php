<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\SalesReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesReminderApiController extends BaseApiController
{
    /**
     * Dapatkan daftar pengingat / notifikasi sales
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = SalesReminder::with(['lead.product', 'admin:id,name', 'sales:id,name', 'commission'])->latest();

        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_completed')) {
            $query->where('is_completed', filter_var($request->is_completed, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $reminders = $query->paginate($perPage);

        $unreadCount = $user->unreadRemindersCount();

        return $this->sendSuccess([
            'unread_count' => $unreadCount,
            'reminders' => $reminders,
        ], 'Daftar pengingat berhasil diambil.');
    }

    /**
     * Tandai notifikasi sudah dibaca
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $reminder = SalesReminder::find($id);

        if (! $reminder) {
            return $this->sendError('Pengingat tidak ditemukan.', 404);
        }

        if ($user->isSales() && $reminder->sales_id !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $reminder->markAsRead();

        return $this->sendSuccess($reminder->fresh(), 'Pengingat ditandai telah dibaca.');
    }

    /**
     * Tandai semua notifikasi sudah dibaca
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        SalesReminder::where('sales_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->sendSuccess(null, 'Semua pengingat telah ditandai dibaca.');
    }

    /**
     * Sales menandai bahwa sudah chat WA ke customer (pengingat selesai)
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $reminder = SalesReminder::with('lead')->find($id);

        if (! $reminder) {
            return $this->sendError('Pengingat tidak ditemukan.', 404);
        }

        if ($user->isSales() && $reminder->sales_id !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'sales_reply' => 'nullable|string|max:500',
        ]);

        $reply = $validated['sales_reply'] ?? null;
        $reminder->markAsCompleted($reply);

        if ($reminder->lead) {
            Activity::create([
                'lead_id' => $reminder->lead_id,
                'user_id' => $user->id,
                'type' => 'whatsapp',
                'subject' => 'Follow-up WhatsApp Customer (Instruksi Selesai)',
                'description' => $reply ?: 'Sales telah menghubungi customer via WhatsApp sesuai instruksi Admin.',
                'activity_at' => now(),
            ]);

            FollowUp::where('lead_id', $reminder->lead_id)
                ->where('assigned_to', $reminder->sales_id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'result_notes' => $reply,
                    'completed_at' => now(),
                ]);

            if (! $reminder->lead->first_contacted_at) {
                $reminder->lead->update(['first_contacted_at' => now()]);
            }
            $reminder->lead->update(['last_contacted_at' => now()]);
        }

        return $this->sendSuccess($reminder->fresh(), 'Pengingat follow-up telah diselesaikan dan aktivitas dicatat.');
    }
}
