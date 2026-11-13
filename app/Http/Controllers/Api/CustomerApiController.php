<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerApiController extends BaseApiController
{
    /**
     * Dapatkan daftar pelanggan (customers) dengan filter & pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['institution:id,name,type', 'lead:id,lead_code,name'])->latest();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($s) => $s->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%")
                ->orWhere('customer_code', 'like', "%$q%")
                ->orWhereHas('institution', fn ($i) => $i->where('name', 'like', "%$q%")));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $customers = $query->paginate($perPage);

        return $this->sendSuccess($customers, 'Daftar pelanggan berhasil dimuat.');
    }

    /**
     * Tambah data pelanggan baru
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:100',
            'institution_id' => 'nullable|exists:institutions,id',
            'lead_id' => 'nullable|exists:leads,id',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $data['customer_code'] = Customer::generateCode();
        $data['source'] = 'api';

        $customer = Customer::create($data);
        $customer->load('institution');

        return $this->sendSuccess($customer, 'Data pelanggan berhasil ditambahkan.', 201);
    }

    /**
     * Dapatkan detail 1 pelanggan
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::with([
            'institution',
            'lead',
            'registrations.product',
            'registrations.schedule.trainer',
        ])->find($id);

        if (! $customer) {
            return $this->sendError('Pelanggan tidak ditemukan.', 404);
        }

        return $this->sendSuccess($customer, 'Detail pelanggan berhasil diambil.');
    }

    /**
     * Perbarui data pelanggan
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return $this->sendError('Pelanggan tidak ditemukan.', 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:100',
            'institution_id' => 'nullable|exists:institutions,id',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'status' => 'sometimes|required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $customer->update($data);
        $customer->load('institution');

        return $this->sendSuccess($customer, 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Hapus data pelanggan (Admin only)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat menghapus data pelanggan.', 403);
        }

        $customer = Customer::find($id);
        if (! $customer) {
            return $this->sendError('Pelanggan tidak ditemukan.', 404);
        }

        $customer->delete();

        return $this->sendSuccess(null, 'Data pelanggan berhasil dihapus.');
    }
}
