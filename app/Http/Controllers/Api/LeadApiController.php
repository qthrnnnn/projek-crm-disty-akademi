<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\SalesReminder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadApiController extends BaseApiController
{
    /**
     * Dapatkan daftar prospek (leads) dengan filter & pagination
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $scope = $request->get('scope', $user->isSales() ? 'my_leads' : 'all');

        $query = Lead::with(['assignedUser:id,name,email,role', 'product:id,name,code,price', 'leadSource:id,name'])->latest();

        if ($scope === 'my_leads' && $user->isSales()) {
            $query->where('assigned_to', $user->id);
        } elseif ($scope === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($scope === 'lost_pool') {
            $query->where('status', 'lost');
            if ($user->isSales()) {
                $query->where('assigned_to', '!=', $user->id);
            }
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($s) => $s
                ->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%")
                ->orWhere('whatsapp', 'like', "%$q%")
                ->orWhere('lead_code', 'like', "%$q%")
                ->orWhere('company', 'like', "%$q%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $leads = $query->paginate($perPage);

        return $this->sendSuccess($leads, 'Daftar leads berhasil dimuat.');
    }

    /**
     * Tambah lead baru
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'product_id' => 'nullable|exists:products,id',
            'deal_price' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        if (empty($data['phone']) && ! empty($data['whatsapp'])) {
            $data['phone'] = $data['whatsapp'];
        } elseif (empty($data['whatsapp']) && ! empty($data['phone'])) {
            $data['whatsapp'] = $data['phone'];
        }

        if ($user->isSales()) {
            $data['assigned_to'] = $user->id;
        }

        $data['lead_code'] = Lead::generateCode();
        $data['status'] = Lead::STATUS_NEW;
        $data['priority'] = $data['priority'] ?? 'normal';
        if (! empty($data['assigned_to'])) {
            $data['assigned_at'] = now();
        }

        $lead = Lead::create($data);
        $lead->load(['assignedUser', 'product', 'leadSource']);

        return $this->sendSuccess($lead, 'Lead prospek berhasil dibuat.', 201);
    }

    /**
     * Dapatkan detail 1 lead beserta aktivitas dan follow-up terkait
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $lead = Lead::with([
            'assignedUser:id,name,email,role,phone',
            'product',
            'leadSource',
            'rejectionReason',
            'activities.user:id,name',
            'followUps.assignedUser:id,name',
            'payments',
            'reminders.admin:id,name',
        ])->find($id);

        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        // Cek otorisasi sales
        if ($user->isSales() && $lead->assigned_to && $lead->assigned_to !== $user->id && $lead->status !== 'lost') {
            return $this->sendError('Anda tidak memiliki akses ke lead sales lain.', 403);
        }

        return $this->sendSuccess($lead, 'Detail lead berhasil diambil.');
    }

    /**
     * Perbarui data lead
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $lead = Lead::find($id);

        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        if ($user->isSales() && $lead->assigned_to !== $user->id) {
            return $this->sendError('Anda hanya boleh mengubah lead milik Anda sendiri.', 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'product_id' => 'nullable|exists:products,id',
            'deal_price' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        if (empty($data['phone']) && ! empty($data['whatsapp'])) {
            $data['phone'] = $data['whatsapp'];
        } elseif (empty($data['whatsapp']) && ! empty($data['phone'])) {
            $data['whatsapp'] = $data['phone'];
        }

        // Hanya admin yang bisa memindahkan assigned_to ke sales lain
        if ($user->isSales()) {
            unset($data['assigned_to']);
        }

        $lead->update($data);
        $lead->load(['assignedUser', 'product', 'leadSource']);

        return $this->sendSuccess($lead, 'Data lead berhasil diperbarui.');
    }

    /**
     * Hapus lead (Soft Delete)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $lead = Lead::find($id);

        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Administrator yang memiliki wewenang menghapus data lead.', 403);
        }

        $lead->delete();

        return $this->sendSuccess(null, 'Lead berhasil dihapus.');
    }

    /**
     * Sales mengklaim lead unassigned atau dari lost pool
     */
    public function claim(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $lead = Lead::find($id);

        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        if ($lead->assigned_to && $lead->status !== 'lost' && $lead->assigned_to !== $user->id) {
            return $this->sendError('Lead ini sedang ditangani oleh sales lain.', 422);
        }

        $lead->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
            'status' => 'new',
        ]);

        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'claim',
            'subject' => 'Lead Diklaim',
            'description' => "Lead diklaim oleh {$user->name} untuk di-followup ulang.",
            'activity_at' => now(),
        ]);

        return $this->sendSuccess($lead->fresh(['assignedUser']), 'Lead berhasil diklaim.');
    }

    /**
     * Perbarui status pipeline prospek (new, contacted, active, negotiation, won, lost)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $lead = Lead::find($id);

        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        if ($user->isSales() && $lead->assigned_to !== $user->id) {
            return $this->sendError('Anda hanya dapat mengubah status lead milik Anda sendiri.', 403);
        }

        $data = $request->validate([
            'status' => 'required|in:new,contacted,active,negotiation,won,lost,inactive',
            'rejection_reason_id' => 'required_if:status,lost|nullable|exists:rejection_reasons,id',
            'rejection_notes' => 'nullable|string',
            'deal_price' => 'nullable|numeric|min:0',
        ]);

        $updatePayload = ['status' => $data['status']];

        if ($data['status'] === 'won') {
            $updatePayload['converted_at'] = now();
            if (isset($data['deal_price'])) {
                $updatePayload['deal_price'] = $data['deal_price'];
            }
        } elseif ($data['status'] === 'lost') {
            $updatePayload['lost_at'] = now();
            $updatePayload['rejection_reason_id'] = $data['rejection_reason_id'] ?? null;
            $updatePayload['rejection_notes'] = $data['rejection_notes'] ?? null;
        }

        if (in_array($data['status'], ['contacted', 'active', 'negotiation', 'won'])) {
            if (! $lead->first_contacted_at) {
                $updatePayload['first_contacted_at'] = now();
            }
            $updatePayload['last_contacted_at'] = now();
        }

        $lead->update($updatePayload);

        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'status_change',
            'subject' => 'Perubahan Status Pipeline',
            'description' => "Status diubah menjadi [{$data['status']}] oleh {$user->name}.",
            'activity_at' => now(),
        ]);

        return $this->sendSuccess($lead->fresh(), 'Status lead berhasil diperbarui.');
    }

    /**
     * Catat aktivitas (call, whatsapp, meeting, email, note) ke lead
     */
    public function storeActivity(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $lead = Lead::find($id);

        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'type' => 'required|in:call,whatsapp,email,meeting,note',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_at' => 'nullable|date',
        ]);

        $activity = Activity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'] ?? null,
            'activity_at' => $validated['activity_at'] ?? now(),
        ]);

        // Perbarui waktu kontak lead
        if (! $lead->first_contacted_at) {
            $lead->update(['first_contacted_at' => now()]);
        }
        $lead->update(['last_contacted_at' => now()]);

        return $this->sendSuccess($activity, 'Aktivitas berhasil dicatat.', 201);
    }

    /**
     * Admin mengirim pengingat instruksi follow up ke sales penanggung jawab lead
     */
    public function remindSales(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat mengirim instruksi pengingat ke Sales.', 403);
        }

        $lead = Lead::with('assignedUser')->find($id);
        if (! $lead) {
            return $this->sendError('Lead tidak ditemukan.', 404);
        }

        if (! $lead->assigned_to) {
            return $this->sendError('Lead ini belum di-assign ke sales manapun.', 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $reminder = SalesReminder::create([
            'lead_id' => $lead->id,
            'admin_id' => $user->id,
            'sales_id' => $lead->assigned_to,
            'type' => 'follow_up',
            'message' => $validated['message'],
            'priority' => 'normal',
            'is_read' => false,
            'is_completed' => false,
        ]);

        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'admin_reminder',
            'subject' => 'Instruksi Admin ke ' . $lead->assignedUser->name,
            'description' => $validated['message'],
            'activity_at' => now(),
        ]);

        return $this->sendSuccess($reminder, 'Instruksi pengingat berhasil dikirim ke sales.', 201);
    }
}
