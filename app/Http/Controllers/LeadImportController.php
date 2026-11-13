<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadImportController extends Controller
{
    public function index()
    {
        $batches = ImportBatch::with('user')->latest()->paginate(15);

        return view('imports.index', compact('batches'));
    }

    public function create()
    {
        $sources = LeadSource::active()->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('imports.create', compact('sources', 'salesUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports', 'local');

        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 1 && ! empty(trim($row[0]))) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        DB::transaction(function () use ($rows, $headers, $path, $file, $request) {
            $batch = ImportBatch::create([
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'total_rows' => count($rows),
                'status' => 'validating',
            ]);

            $success = 0;
            $failed = 0;

            foreach ($rows as $i => $row) {
                $data = @array_combine($headers ?? [], $row) ?: ['name' => $row[0] ?? ''];
                $errors = [];
                $name = trim($data['name'] ?? $data['Name'] ?? $data['NAMA'] ?? ($row[0] ?? ''));

                if (empty($name)) {
                    $errors[] = 'Nama tidak boleh kosong';
                    $failed++;
                } else {
                    $success++;
                }

                ImportRow::create([
                    'import_batch_id' => $batch->id,
                    'row_number' => $i + 2,
                    'data' => $data,
                    'status' => empty($errors) ? 'valid' : 'invalid',
                    'errors' => $errors ?: null,
                ]);
            }

            // Auto-execute valid rows
            $validRows = ImportRow::where('import_batch_id', $batch->id)
                ->where('status', 'valid')->get();

            foreach ($validRows as $importRow) {
                $d = $importRow->data;
                $name = $d['name'] ?? $d['Name'] ?? $d['NAMA'] ?? '';
                $lead = Lead::create([
                    'lead_code' => Lead::generateCode(),
                    'name' => $name,
                    'email' => $d['email'] ?? $d['Email'] ?? null,
                    'phone' => $d['phone'] ?? $d['Phone'] ?? $d['Telepon'] ?? null,
                    'whatsapp' => $d['whatsapp'] ?? $d['WhatsApp'] ?? null,
                    'company' => $d['company'] ?? $d['Company'] ?? $d['Perusahaan'] ?? null,
                    'city' => $d['city'] ?? $d['City'] ?? $d['Kota'] ?? null,
                    'lead_source_id' => $request->lead_source_id,
                    'assigned_to' => $request->assigned_to,
                    'assigned_at' => $request->assigned_to ? now() : null,
                    'status' => 'new',
                    'priority' => 'normal',
                ]);
                $importRow->update(['status' => 'imported', 'lead_id' => $lead->id]);
            }

            $batch->update([
                'success_rows' => $success,
                'failed_rows' => $failed,
                'status' => 'completed',
            ]);
        });

        return redirect()->route('lead-imports.index')->with('success', 'Import data lead berhasil diproses.');
    }

    public function show(ImportBatch $importBatch)
    {
        $importBatch->load('user');
        $rows = ImportRow::where('import_batch_id', $importBatch->id)
            ->with('lead')->paginate(25);

        return view('imports.show', compact('importBatch', 'rows'));
    }
}
