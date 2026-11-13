<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Institution;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('institution')->latest();

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

        $perPage = (int) $request->get('per_page', 20);
        $customers = $query->paginate($perPage)->withQueryString();
        $institutions = Institution::orderBy('name')->get();

        return view('customers.index', compact('customers', 'institutions'));
    }

    public function create()
    {
        $institutions = Institution::orderBy('name')->get();

        return view('customers.create', compact('institutions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:100',
            'institution_id' => 'nullable|exists:institutions,id',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'status' => 'required|in:active',
            'notes' => 'nullable|string',
        ]);

        $data['customer_code'] = Customer::generateCode();
        $data['source'] = 'manual';

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil didaftarkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['institution', 'registrations.product', 'registrations.schedule.trainer']);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $institutions = Institution::orderBy('name')->get();

        return view('customers.edit', compact('customer', 'institutions'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:100',
            'institution_id' => 'nullable|exists:institutions,id',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'status' => 'required|in:active',
            'notes' => 'nullable|string',
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Data Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }

    /**
     * Export Customer ke CSV sesuai 8 ketentuan kolom di gambar:
     * 1. Nama, 2. Email, 3. Nomor Telepon, 4. Nomor Dokumen,
     * 5. Nama Instansi, 6. Jenis Instansi, 7. Kota, 8. Provinsi
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'customers_export_'.date('Ymd_His').'.csv';
        $customers = Customer::with('institution')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header 8 kolom persis gambar
            fputcsv($handle, [
                'Nama',
                'Email',
                'Nomor Telepon',
                'Nomor Dokumen',
                'Nama Instansi',
                'Jenis Instansi',
                'Kota',
                'Provinsi',
            ]);

            foreach ($customers as $c) {
                fputcsv($handle, [
                    $c->name,
                    $c->email ?? '',
                    $c->phone ?? '',
                    $c->document_number ?? '',
                    $c->institution?->name ?? '',
                    $c->institution?->type ?? '',
                    $c->city ?? '',
                    $c->province ?? '',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import Customer dari file CSV sesuai 8 kolom ketentuan gambar:
     * 1. Nama, 2. Email, 3. Nomor Telepon, 4. Nomor Dokumen,
     * 5. Nama Instansi, 6. Jenis Instansi, 7. Kota, 8. Provinsi
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->with('error', 'Gagal membaca file CSV.');
        }

        // Handle possible UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Minimal 1 kolom (Nama)
            $name = trim($row[0] ?? '');
            if (empty($name)) {
                $skipped++;

                continue;
            }

            $email = ! empty($row[1]) ? trim($row[1]) : null;
            $phone = ! empty($row[2]) ? trim($row[2]) : null;
            $docNumber = ! empty($row[3]) ? trim($row[3]) : null;
            $instName = ! empty($row[4]) ? trim($row[4]) : null;
            $instType = ! empty($row[5]) ? trim($row[5]) : null;
            $city = ! empty($row[6]) ? trim($row[6]) : null;
            $province = ! empty($row[7]) ? trim($row[7]) : null;

            // Cari atau buat instansi jika diisi
            $institutionId = null;
            if ($instName) {
                $institution = Institution::firstOrCreate(
                    ['name' => $instName],
                    [
                        'type' => $instType ?: 'Umum',
                        'city' => $city,
                        'province' => $province,
                    ]
                );
                $institutionId = $institution->id;
            }

            // Simpan Customer
            Customer::create([
                'customer_code' => Customer::generateCode(),
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'document_number' => $docNumber,
                'institution_id' => $institutionId,
                'city' => $city,
                'province' => $province,
                'status' => 'active',
                'source' => 'import_csv',
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('customers.index')->with('success', "Import selesai: $imported data customer berhasil dimasukkan".($skipped > 0 ? " ($skipped baris kosong diabaikan)" : '').'.');
    }

    /**
     * Download format template CSV 8 kolom persis ketentuan gambar
     */
    public function downloadTemplate(): StreamedResponse
    {
        $fileName = 'template_import_customer.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'Nama',
                'Email',
                'Nomor Telepon',
                'Nomor Dokumen',
                'Nama Instansi',
                'Jenis Instansi',
                'Kota',
                'Provinsi',
            ]);
            // Contoh baris seperti gambar
            fputcsv($handle, [
                'Budi Santoso',
                'budi@gmail.com',
                '081234567890',
                '1234567890',
                'Universitas XYZ',
                'Perguruan Tinggi',
                'Surabaya',
                'Jawa Timur',
            ]);
            fclose($handle);
        }, 200, $headers);
    }
}
