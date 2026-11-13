<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\SalesReminder;
use App\Models\User;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $query = FollowUp::with(['lead', 'assignedUser'])->latest('follow_up_at');

        if (auth()->user()->isSales()) {
            $query->where('assigned_to', auth()->id());
        } elseif ($request->filled('assigned')) {
            $query->where('assigned_to', $request->assigned);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $followUps = $query->paginate(20)->withQueryString();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('follow-ups.index', compact('followUps', 'salesUsers'));
    }

    public function create(Request $request)
    {
        $leadsQuery = Lead::whereNotIn('status', ['won', 'lost'])->orderBy('name');
        if (auth()->user()->isSales()) {
            $leadsQuery->where('assigned_to', auth()->id());
        }
        $leads = $leadsQuery->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();
        $selectedLead = $request->filled('lead_id') ? Lead::find($request->lead_id) : null;

        return view('follow-ups.create', compact('leads', 'salesUsers', 'selectedLead'));
    }

    public function store(Request $request)
    {
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
        if (auth()->user()->isSales()) {
            if ($lead->assigned_to && $lead->assigned_to !== auth()->id()) {
                return back()->with('error', 'Akses ditolak: Anda hanya dapat membuat jadwal follow-up untuk lead yang ditugaskan kepada Anda.');
            }
            $data['assigned_to'] = auth()->id();
        } else {
            $data['assigned_to'] = $data['assigned_to'] ?? $lead->assigned_to;
        }

        $data['priority'] = $data['priority'] ?? 'normal';

        $followUp = FollowUp::create($data);

        // Jika dibuat oleh Admin untuk Sales, otomatis buat notifikasi pengingat ke Sales tersebut
        if (auth()->user()->isAdmin() && ! empty($data['assigned_to'])) {
            $salesTarget = User::find($data['assigned_to']);
            if ($salesTarget && $salesTarget->isSales()) {
                SalesReminder::create([
                    'lead_id' => $lead->id,
                    'admin_id' => auth()->id(),
                    'sales_id' => $salesTarget->id,
                    'message' => $data['title'].($data['description'] ? ' — '.$data['description'] : ''),
                    'priority' => in_array($data['priority'] ?? 'normal', ['high', 'urgent']) ? 'urgent' : 'normal',
                    'is_read' => false,
                    'is_completed' => false,
                ]);
            }
        }

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up berhasil ditambahkan.');
    }

    public function edit(FollowUp $followUp)
    {
        if (auth()->user()->isSales() && $followUp->assigned_to !== auth()->id()) {
            return redirect()->route('follow-ups.index')->with('error', 'Akses dibatasi: Anda hanya dapat mengedit follow-up milik Anda.');
        }

        $leadsQuery = Lead::whereNotIn('status', ['won', 'lost'])->orderBy('name');
        if (auth()->user()->isSales()) {
            $leadsQuery->where('assigned_to', auth()->id());
        }
        $leads = $leadsQuery->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('follow-ups.edit', compact('followUp', 'leads', 'salesUsers'));
    }

    public function update(Request $request, FollowUp $followUp)
    {
        if (auth()->user()->isSales() && $followUp->assigned_to !== auth()->id()) {
            return back()->with('error', 'Akses dibatasi: Anda tidak dapat mengubah follow-up milik sales lain.');
        }

        $data = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'follow_up_at' => 'required|date',
            'method' => 'required|in:whatsapp,call,email,meeting',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data['priority'] = $data['priority'] ?? ($followUp->priority ?? 'normal');
        if (auth()->user()->isSales()) {
            $data['assigned_to'] = auth()->id();
        }

        $followUp->update($data);

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up berhasil diperbarui.');
    }

    public function destroy(FollowUp $followUp)
    {
        $followUp->delete();

        return redirect()->route('follow-ups.index')->with('success', 'Follow-up dihapus.');
    }

    public function complete(Request $request, FollowUp $followUp)
    {
        $followUp->update([
            'status' => 'completed',
            'result_notes' => $request->result_notes,
            'completed_at' => now(),
        ]);

        // Tandai SalesReminder terkait juga selesai
        SalesReminder::where('lead_id', $followUp->lead_id)
            ->where('sales_id', $followUp->assigned_to)
            ->where('is_completed', false)
            ->update([
                'is_read' => true,
                'is_completed' => true,
                'completed_at' => now(),
                'sales_reply' => $request->result_notes,
            ]);

        return back()->with('success', 'Follow-up ditandai selesai.');
    }

    public function cancel(FollowUp $followUp)
    {
        $followUp->update(['status' => 'cancelled']);

        return back()->with('success', 'Follow-up dibatalkan.');
    }
}
