<?php

namespace App\Http\Controllers\Api;

use App\Models\Institution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitutionApiController extends BaseApiController
{
    /**
     * Daftar instansi mitra / kampus / korporat
     */
    public function index(Request $request): JsonResponse
    {
        $query = Institution::withCount('customers')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($s) => $s->where('name', 'like', "%$q%")
                ->orWhere('city', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%"));
        }

        $institutions = $query->paginate(min((int) $request->get('per_page', 20), 100));

        return $this->sendSuccess($institutions, 'Daftar instansi berhasil diambil.');
    }

    /**
     * Tambah instansi mitra baru
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat menambah instansi.', 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $institution = Institution::create($data);

        return $this->sendSuccess($institution, 'Instansi berhasil ditambahkan.', 201);
    }

    /**
     * Detail instansi
     */
    public function show(int $id): JsonResponse
    {
        $institution = Institution::with('customers')->find($id);

        if (! $institution) {
            return $this->sendError('Instansi tidak ditemukan.', 404);
        }

        return $this->sendSuccess($institution, 'Detail instansi berhasil diambil.');
    }
}
