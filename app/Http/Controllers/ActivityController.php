<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['lead', 'user'])->visibleTo($request->user())->latest('activity_at')->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $activities = $query->paginate(25)->withQueryString();

        return view('activities.index', compact('activities'));
    }

    public function store(Request $request, Lead $lead)
    {
        if (auth()->user()->isSales() && $lead->assigned_to && $lead->assigned_to !== auth()->id()) {
            return back()->with('error', 'Akses dibatasi: Lead ini sedang ditangani oleh sales lain. Anda tidak dapat menambahkan aktivitas.');
        }

        $data = $request->validate([
            'type' => 'required|in:call,email,whatsapp,meeting,note,status_change',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'activity_at' => 'required|date',
        ]);

        $data['lead_id'] = $lead->id;
        $data['user_id'] = auth()->id();

        Activity::create($data);

        // Jika lead belum bertuan dan sales mencatat aktivitas, otomatis klaim lead
        if (auth()->user()->isSales() && ! $lead->assigned_to) {
            $lead->update(['assigned_to' => auth()->id(), 'assigned_at' => now()]);
        }

        // Update last_contacted_at on lead
        if (! $lead->first_contacted_at) {
            $lead->update(['first_contacted_at' => now(), 'status' => 'contacted']);
        }
        $lead->update(['last_contacted_at' => now()]);

        return back()->with('success', 'Aktivitas berhasil dicatat.');
    }
}
