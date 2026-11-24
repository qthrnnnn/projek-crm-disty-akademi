<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\SalesReminder;
use App\Models\User;
use Illuminate\Http\Request;

class SalesReminderController extends Controller
{
    /**
     * Tampilkan daftar pengingat follow up
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SalesReminder::with(['lead.product', 'admin', 'sales', 'commission'])->latest();

        if ($user->isSales()) {
            $query->where('sales_id', $user->id);

            // Karena sales sudah membuka halaman pengingat, tandai semua unread sebagai sudah dibaca
            SalesReminder::where('sales_id', $user->id)
                ->where('is_read', false)
                ->updateWithHistory([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        } elseif ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('type', 'follow_up')->where('is_completed', false);
            } elseif ($request->status === 'completed') {
                $query->where('type', 'follow_up')->where('is_completed', true);
            }
        }

        $reminders = $query->paginate(15)->withQueryString();

        $baseCountQuery = $user->isSales()
            ? SalesReminder::where('sales_id', $user->id)
            : SalesReminder::query();

        $pendingCount = (clone $baseCountQuery)->where('type', 'follow_up')->where('is_completed', false)->count();
        $completedCount = (clone $baseCountQuery)->where('type', 'follow_up')->where('is_completed', true)->count();
        $commissionCount = (clone $baseCountQuery)->where('type', 'commission_paid')->count();

        return view('reminders.index', compact('reminders', 'pendingCount', 'completedCount', 'commissionCount'));
    }

    /**
     * Admin mengirimkan pengingat ke Sales agar segera follow up / chat customer
     */
    public function store(Request $request, Lead $lead)
    {
        if (! auth()->user()->isAdmin()) {
            return back()->with('error', 'Akses ditolak: Hanya Admin yang dapat mengirim instruksi pengingat ke Sales.');
        }

        if (! $request->filled('sales_id') && $lead->assigned_to) {
            $request->merge(['sales_id' => $lead->assigned_to]);
        }

        $validated = $request->validate([
            'sales_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string', 'max:1000'],
            'priority' => ['nullable', 'in:normal,urgent'],
        ]);

        $sales = User::findOrFail($validated['sales_id']);
        if (! $sales->isSales()) {
            return back()->with('error', 'Pengguna yang dipilih bukan akun Sales.');
        }

        // Jika lead belum di-assign atau berbeda, assign ke sales ini
        if ($lead->assigned_to !== $sales->id) {
            $lead->update([
                'assigned_to' => $sales->id,
                'assigned_at' => now(),
            ]);
        }

        $priority = $validated['priority'] ?? 'urgent';
        $dueDate = $request->filled('due_date') ? \Carbon\Carbon::parse($request->due_date) : now()->addWeek();

        // Buat record pengingat
        $reminder = SalesReminder::create([
            'lead_id' => $lead->id,
            'admin_id' => auth()->id(),
            'sales_id' => $sales->id,
            'message' => $validated['message'],
            'priority' => $priority,
            'is_read' => false,
            'is_completed' => false,
        ]);

        // Otomatis buat jadwal FollowUp untuk Sales dengan batas waktu 1 minggu (atau sesuai due_date)
        FollowUp::create([
            'lead_id' => $lead->id,
            'assigned_to' => $sales->id,
            'title' => 'Pengingat Follow-up Admin',
            'description' => $validated['message'],
            'follow_up_at' => $dueDate,
            'method' => 'whatsapp',
            'priority' => $priority === 'urgent' ? 'high' : 'normal',
            'status' => 'pending',
        ]);

        // Rekam di timeline aktivitas lead
        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'admin_reminder',
            'subject' => 'Instruksi Admin ke ' . $sales->name,
            'description' => $validated['message'],
            'activity_at' => now(),
        ]);

        return back()->with('success', "Pengingat follow-up berhasil dikirim ke {$sales->name}! Sales akan menerima badge notifikasi lonceng & instruksi chat.");
    }

    /**
     * Tandai pengingat sudah dibaca
     */
    public function markRead(SalesReminder $reminder)
    {
        $user = auth()->user();
        if ($user->id !== $reminder->sales_id && ! $user->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $reminder->markAsRead();

        return back()->with('success', 'Pengingat ditandai telah dibaca.');
    }

    /**
     * Sales menandai bahwa sudah chat ke customer (pengingat selesai)
     */
    public function markCompleted(Request $request, SalesReminder $reminder)
    {
        $user = auth()->user();
        if ($user->id !== $reminder->sales_id && ! $user->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'sales_reply' => ['nullable', 'string', 'max:500'],
        ]);

        $salesReply = $validated['sales_reply'] ?? $request->input('sales_reply') ?? null;

        $reminder->markAsCompleted($salesReply);

        // Rekam aktivitas chat WA di Lead
        Activity::create([
            'lead_id' => $reminder->lead_id,
            'user_id' => $user->id,
            'type' => 'whatsapp',
            'subject' => 'Follow-up WhatsApp Customer (Instruksi Selesai)',
            'description' => $salesReply ?: 'Sales telah menghubungi customer via WhatsApp sesuai instruksi Admin.',
            'activity_at' => now(),
        ]);

        // Jika ada jadwal FollowUp pending terkait lead ini & sales ini, tandai selesai juga
        FollowUp::where('lead_id', $reminder->lead_id)
            ->where('assigned_to', $reminder->sales_id)
            ->where('status', 'pending')
            ->updateWithHistory([
                'status' => 'completed',
                'result_notes' => $salesReply,
                'completed_at' => now(),
            ]);

        // Update waktu kontak lead
        if (! $reminder->lead->first_contacted_at) {
            $reminder->lead->update(['first_contacted_at' => now()]);
        }
        $reminder->lead->update(['last_contacted_at' => now()]);

        return back()->with('success', 'Hebat! Pengingat follow-up telah diselesaikan dan aktivitas telah dicatat.');
    }

    /**
     * Tandai semua notifikasi sudah dibaca
     */
    public function markAllRead(Request $request)
    {
        $user = auth()->user();
        SalesReminder::where('sales_id', $user->id)
            ->where('is_read', false)
            ->updateWithHistory([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($request->expectsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
