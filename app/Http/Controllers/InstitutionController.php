<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institution::withCount('customers')->latest();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($s) => $s->where('name', 'like', "%$q%")
                ->orWhere('city', 'like', "%$q%")
                ->orWhere('type', 'like', "%$q%"));
        }

        $institutions = $query->paginate(20)->withQueryString();

        return view('institutions.index', compact('institutions'));
    }

    public function create()
    {
        return view('institutions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        Institution::create($data);

        return redirect()->route('institutions.index')->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function edit(Institution $institution)
    {
        return view('institutions.edit', compact('institution'));
    }

    public function update(Request $request, Institution $institution)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $institution->update($data);

        return redirect()->route('institutions.index')->with('success', 'Instansi berhasil diperbarui.');
    }

    public function destroy(Institution $institution)
    {
        $institution->delete();

        return redirect()->route('institutions.index')->with('success', 'Instansi berhasil dihapus.');
    }
}
