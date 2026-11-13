<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Registration;
use App\Models\TrainingSchedule;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['customer', 'product', 'schedule.trainer'])->latest();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('registration_number', 'like', "%$q%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$q%"))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%$q%"))
                    ->orWhereHas('schedule.trainer', fn ($t) => $t->where('name', 'like', "%$q%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', 50);
        $registrations = $query->paginate($perPage)->withQueryString();

        return view('registrations.index', compact('registrations'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $schedules = TrainingSchedule::with(['product', 'trainer'])
            ->where('status', 'scheduled')
            ->where('training_date', '>=', now()->toDateString())
            ->orderBy('training_date')
            ->get();

        $selectedCustomer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;
        $selectedSchedule = $request->filled('schedule_id') ? TrainingSchedule::find($request->schedule_id) : null;

        return view('registrations.create', compact('customers', 'schedules', 'selectedCustomer', 'selectedSchedule'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'training_schedule_id' => 'required|exists:training_schedules,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:registered,confirmed,attended,cancelled',
            'notes' => 'nullable|string',
        ]);

        $schedule = TrainingSchedule::findOrFail($data['training_schedule_id']);

        $data['registration_number'] = Registration::generateNumber();
        $data['product_id'] = $schedule->product_id;
        $data['training_date'] = $schedule->training_date;

        Registration::create($data);

        return redirect()->route('registrations.index')->with('success', 'Customer berhasil didaftarkan ke jadwal pelatihan.');
    }

    public function edit(Registration $registration)
    {
        $customers = Customer::orderBy('name')->get();
        $schedules = TrainingSchedule::with(['product', 'trainer'])
            ->orderByDesc('training_date')
            ->get();

        return view('registrations.edit', compact('registration', 'customers', 'schedules'));
    }

    public function update(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'training_schedule_id' => 'required|exists:training_schedules,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:registered,confirmed,attended,cancelled',
            'notes' => 'nullable|string',
        ]);

        $schedule = TrainingSchedule::findOrFail($data['training_schedule_id']);
        $data['product_id'] = $schedule->product_id;
        $data['training_date'] = $schedule->training_date;

        $registration->update($data);

        return redirect()->route('registrations.index')->with('success', 'Data pendaftaran peserta berhasil diperbarui.');
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return redirect()->route('registrations.index')->with('success', 'Pendaftaran peserta berhasil dihapus.');
    }
}
