<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends BaseApiController
{
    /**
     * Dapatkan daftar katalog produk / program sertifikasi
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($s) => $s->where('name', 'like', "%$q%")
                ->orWhere('code', 'like', "%$q%")
                ->orWhere('description', 'like', "%$q%"));
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $products = $query->paginate($perPage);

        return $this->sendSuccess($products, 'Katalog produk berhasil diambil.');
    }

    /**
     * Tambah program/produk baru (Admin Only)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat menambah katalog produk.', 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'product_type' => 'required|in:training,certification,bundle',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        $product = Product::create($data);

        return $this->sendSuccess($product, 'Produk berhasil ditambahkan ke katalog.', 201);
    }

    /**
     * Detail 1 produk
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->sendError('Produk tidak ditemukan.', 404);
        }

        return $this->sendSuccess($product, 'Detail produk berhasil diambil.');
    }

    /**
     * Perbarui data produk (Admin Only)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat mengubah produk.', 403);
        }

        $product = Product::find($id);
        if (! $product) {
            return $this->sendError('Produk tidak ditemukan.', 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => "sometimes|required|string|max:50|unique:products,code,{$id}",
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'product_type' => 'sometimes|required|in:training,certification,bundle',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $product->update($data);

        return $this->sendSuccess($product, 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk (Admin Only)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return $this->sendError('Hanya Admin yang dapat menghapus produk.', 403);
        }

        $product = Product::find($id);
        if (! $product) {
            return $this->sendError('Produk tidak ditemukan.', 404);
        }

        $product->delete();

        return $this->sendSuccess(null, 'Produk berhasil dihapus.');
    }
}
