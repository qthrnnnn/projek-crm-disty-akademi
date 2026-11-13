<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use Illuminate\Http\Request;

class TrainingScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingSchedule::with(['product', 'trainer'])->withCount('registrations')->latest('training_date');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('location', 'like', "%$q%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%$q%"))
                    ->orWhereHas('trainer', fn ($t) => $t->where('name', 'like', "%$q%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->paginate(20)->withQueryString();

        return view('training-schedules.index', compact('schedules'));
    }

    public function create()
    {
        $products = Product::active()->get();
        $trainers = Trainer::where('is_active', true)->get();

        return view('training-schedules.create', compact('products', 'trainers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'trainer_id' => 'required|exists:trainers,id',
            'training_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        TrainingSchedule::create($data);

        return redirect()->route('training-schedules.index')->with('success', 'Jadwal pelatihan berhasil dibuat.');
    }

    public function edit(TrainingSchedule $trainingSchedule)
    {
        $products = Product::active()->get();
        $trainers = Trainer::where('is_active', true)->get();

        return view('training-schedules.edit', compact('trainingSchedule', 'products', 'trainers'));
    }

    public function update(Request $request, TrainingSchedule $trainingSchedule)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'trainer_id' => 'required|exists:trainers,id',
            'training_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $trainingSchedule->update($data);

        return redirect()->route('training-schedules.index')->with('success', 'Jadwal pelatihan berhasil diperbarui.');
    }

    public function destroy(TrainingSchedule $trainingSchedule)
    {
        $trainingSchedule->delete();

        return redirect()->route('training-schedules.index')->with('success', 'Jadwal pelatihan dihapus.');
    }
}
