<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowUpApiController extends BaseApiController
{
    /**
     * Dapatkan daftar follow-up
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = FollowUp::with(['lead:id,lead_code,name,phone,whatsapp,status', 'assignedUser:id,name'])->latest('follow_up_at');

        if ($user->isSales()) {
            $query->where('assigned_to', $user->id);
        } elseif ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('follow_up_at', $request->date);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $followUps = $query->paginate($perPage);

        return $this->sendSuccess($followUps, 'Daftar jadwal follow-up berhasil dimuat.');
    }

    /**
     * Buat jadwal follow-up baru
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'follow_up_at' => 'required|date',
            'method' => 'required|in:whatsapp,call,email,meeting',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = Lead::findOrFail($data['lead_id']);

        if ($user->isSales()) {
            if ($lead->assigned_to && $lead->assigned_to !== $user->id) {
                return $this->sendError('Anda tidak boleh membuat follow-up untuk lead milik sales lain.', 403);
            }
            $data['assigned_to'] = $user->id;
        } else {
            $data['assigned_to'] = $data['assigned_to'] ?? $lead->assigned_to ?? $user->id;
        }

        $data['status'] = 'pending';
        $data['priority'] = $data['priority'] ?? 'normal';

        $followUp = FollowUp::create($data);
        $followUp->load(['lead', 'assignedUser']);

        return $this->sendSuccess($followUp, 'Jadwal follow-up berhasil dibuat.', 201);
    }

    /**
     * Dapatkan detail 1 follow-up
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $followUp = FollowUp::with(['lead', 'assignedUser'])->find($id);

        if (! $followUp) {
            return $this->sendError('Jadwal follow-up tidak ditemukan.', 404);
        }

        if ($user->isSales() && $followUp->assigned_to !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        return $this->sendSuccess($followUp, 'Detail follow-up berhasil diambil.');
    }

    /**
     * Perbarui jadwal follow-up
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $followUp = FollowUp::find($id);

        if (! $followUp) {
            return $this->sendError('Follow-up tidak ditemukan.', 404);
        }

        if ($user->isSales() && $followUp->assigned_to !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'follow_up_at' => 'sometimes|required|date',
            'method' => 'sometimes|required|in:whatsapp,call,email,meeting',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        $followUp->update($data);

        return $this->sendSuccess($followUp->fresh(['lead', 'assignedUser']), 'Follow-up berhasil diperbarui.');
    }

    /**
     * Selesaikan follow-up (complete)
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $followUp = FollowUp::with('lead')->find($id);

        if (! $followUp) {
            return $this->sendError('Follow-up tidak ditemukan.', 404);
        }

        if ($user->isSales() && $followUp->assigned_to !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'result_notes' => 'nullable|string',
        ]);

        $followUp->update([
            'status' => 'completed',
            'result_notes' => $validated['result_notes'] ?? null,
            'completed_at' => now(),
        ]);

        // Rekam aktivitas
        Activity::create([
            'lead_id' => $followUp->lead_id,
            'user_id' => $user->id,
            'type' => $followUp->method,
            'subject' => "Follow-up Selesai: {$followUp->title}",
            'description' => $validated['result_notes'] ?? 'Follow up selesai dilaksanakan.',
            'activity_at' => now(),
        ]);

        // Update kontak lead
        if ($followUp->lead) {
            if (! $followUp->lead->first_contacted_at) {
                $followUp->lead->update(['first_contacted_at' => now()]);
            }
            $followUp->lead->update(['last_contacted_at' => now()]);
        }

        return $this->sendSuccess($followUp->fresh(), 'Follow-up berhasil diselesaikan.');
    }

    /**
     * Batalkan jadwal follow-up
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $followUp = FollowUp::find($id);

        if (! $followUp) {
            return $this->sendError('Follow-up tidak ditemukan.', 404);
        }

        if ($user->isSales() && $followUp->assigned_to !== $user->id) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $validated = $request->validate([
            'result_notes' => 'nullable|string',
        ]);

        $followUp->update([
            'status' => 'cancelled',
            'result_notes' => $validated['result_notes'] ?? 'Dibatalkan',
            'completed_at' => now(),
        ]);

        return $this->sendSuccess($followUp->fresh(), 'Follow-up berhasil dibatalkan.');
    }
}
