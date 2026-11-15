<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Product;
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
        $user = auth()->user();
        if ($user && ! $user->isSales()) {
            return redirect()->route('lead-imports.index')
                ->with('error', 'Akses ditolak. Pengunggahan file Excel leads hanya dapat dilakukan oleh Sales. Akun Admin hanya memiliki akses pemantauan.');
        }

        $sources = LeadSource::active()->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('imports.create', compact('sources', 'salesUsers'));
    }

    public function store(Request $request)
    {
        $user = $request->user() ?: auth()->user();

        if (! $user) {
            $msg = 'Autentikasi gagal. Silakan login kembali.';
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $msg], 401);
            }

            return redirect()->route('login')->with('error', $msg);
        }

        // Aturan Otomatis: Sales yang mengunggah berkas Excel otomatis menjadi Sales PIC seluruh prospek
        if ($user->isSales()) {
            $salesId = $user->id;
            $salesName = $user->name;
        } else {
            // Jika akun Admin yang mengunggah (misal saat demo), berikan opsi menugaskan ke sales tertentu atau sales pertama
            $targetId = $request->input('sales_id', $request->input('user_id'));
            $salesUser = $targetId ? User::find($targetId) : User::where('role', 'sales')->where('is_active', true)->first();
            $salesId = $salesUser?->id ?? $user->id;
            $salesName = $salesUser?->name ?? $user->name;
        }

        $request->validate([
            'file' => 'required|file|max:10240',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'source_id' => 'nullable|exists:lead_sources,id',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];
        if ($ext === 'xlsx') {
            $rows = $this->parseXlsxFile($path);
        } elseif (in_array($ext, ['csv', 'txt'])) {
            $rows = $this->parseCsvFile($path);
        } else {
            $err = 'Format berkas tidak didukung. Harap unggah file spreadsheet .xlsx (Excel) atau .csv.';
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $err], 422);
            }

            return back()->with('error', $err);
        }

        if (empty($rows)) {
            $err = 'File kosong atau tidak berisi data yang dapat dibaca.';
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $err], 422);
            }

            return back()->with('error', $err);
        }

        $storedPath = $file->store('imports', 'local');
        $rawHeaders = array_shift($rows);
        $headerMap = [];
        foreach ($rawHeaders as $idx => $colName) {
            $norm = $this->normalizeHeader((string) $colName);
            if ($norm) {
                $headerMap[$norm] = $idx;
            }
        }

        $sourceId = $request->lead_source_id ?: $request->source_id;
        if (! $sourceId) {
            $sourceName = in_array($ext, ['csv', 'txt']) ? 'import_csv' : 'import_excel';
            $source = LeadSource::where('name', $sourceName)->first()
                ?? LeadSource::whereIn('name', ['import_excel', 'import_csv'])->first()
                ?? LeadSource::first();
            $sourceId = $source?->id;
        }

        // Aturan Eksklusif: Seluruh data lead otomatis ditugaskan ke Sales yang mengunggah
        $salesId = $user->id;
        $salesName = $user->name;

        $batch = null;
        $success = 0;
        $failed = 0;

        DB::transaction(function () use ($rows, $headerMap, $storedPath, $file, $sourceId, $salesId, $salesName, &$batch, &$success, &$failed) {
            $batch = ImportBatch::create([
                'user_id' => $salesId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'total_rows' => count($rows),
                'status' => 'validating',
            ]);

            foreach ($rows as $i => $row) {
                $nameIdx = $headerMap['name'] ?? 0;
                $name = trim($row[$nameIdx] ?? '');

                if (empty($name)) {
                    $failed++;
                    ImportRow::create([
                        'import_batch_id' => $batch->id,
                        'row_number' => $i + 2,
                        'data' => $row,
                        'status' => 'invalid',
                        'errors' => ['Nama tidak boleh kosong'],
                    ]);

                    continue;
                }

                $whatsapp = isset($headerMap['whatsapp']) && isset($row[$headerMap['whatsapp']]) ? trim($row[$headerMap['whatsapp']]) : null;
                $phone = isset($headerMap['phone']) && isset($row[$headerMap['phone']]) ? trim($row[$headerMap['phone']]) : null;
                $email = isset($headerMap['email']) && isset($row[$headerMap['email']]) ? trim($row[$headerMap['email']]) : null;
                $company = isset($headerMap['company']) && isset($row[$headerMap['company']]) ? trim($row[$headerMap['company']]) : null;
                $jobTitle = isset($headerMap['job_title']) && isset($row[$headerMap['job_title']]) ? trim($row[$headerMap['job_title']]) : null;
                $city = isset($headerMap['city']) && isset($row[$headerMap['city']]) ? trim($row[$headerMap['city']]) : null;
                $province = isset($headerMap['province']) && isset($row[$headerMap['province']]) ? trim($row[$headerMap['province']]) : null;
                $prodName = isset($headerMap['product']) && isset($row[$headerMap['product']]) ? trim($row[$headerMap['product']]) : null;
                $nik = isset($headerMap['nik']) && isset($row[$headerMap['nik']]) ? trim($row[$headerMap['nik']]) : null;
                $birthDate = isset($headerMap['birth_date']) && isset($row[$headerMap['birth_date']]) ? trim($row[$headerMap['birth_date']]) : null;
                $notes = isset($headerMap['notes']) && isset($row[$headerMap['notes']]) ? trim($row[$headerMap['notes']]) : null;

                if (empty($phone) && ! empty($whatsapp)) {
                    $phone = $whatsapp;
                } elseif (empty($whatsapp) && ! empty($phone)) {
                    $whatsapp = $phone;
                }

                $productId = null;
                if (! empty($prodName)) {
                    $prod = Product::where('name', 'like', "%{$prodName}%")
                        ->orWhere('code', 'like', "%{$prodName}%")
                        ->first();
                    $productId = $prod?->id;
                }

                $lead = Lead::create([
                    'lead_code' => Lead::generateCode(),
                    'name' => $name,
                    'nik' => $nik,
                    'birth_date' => $birthDate ? date('Y-m-d', strtotime($birthDate)) : null,
                    'email' => $email,
                    'phone' => $phone,
                    'whatsapp' => $whatsapp,
                    'company' => $company,
                    'job_title' => $jobTitle,
                    'city' => $city,
                    'province' => $province,
                    'product_id' => $productId,
                    'lead_source_id' => $sourceId,
                    'assigned_to' => $salesId,
                    'assigned_at' => now(),
                    'status' => 'new',
                    'priority' => 'normal',
                    'notes' => $notes,
                    'created_at' => now(),
                ]);

                if (! empty($company)) {
                    Institution::firstOrCreate(
                        ['name' => $company],
                        [
                            'type' => 'company',
                            'city' => $city,
                            'province' => $province,
                            'address' => $city ? "{$city}, {$province}" : null,
                        ]
                    );
                }

                ImportRow::create([
                    'import_batch_id' => $batch->id,
                    'row_number' => $i + 2,
                    'data' => [
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'company' => $company,
                    ],
                    'status' => 'imported',
                    'lead_id' => $lead->id,
                ]);

                // Log riwayat aktivitas penugasan otomatis ke Sales PIC pengunggah
                Activity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $salesId,
                    'type' => 'note',
                    'subject' => 'Import Leads (Excel)',
                    'description' => "Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke {$salesName} sebagai Sales PIC.",
                    'activity_at' => now(),
                ]);

                $success++;
            }

            $batch->update([
                'success_rows' => $success,
                'failed_rows' => $failed,
                'status' => 'completed',
            ]);
        });

        $msg = "Import data lead selesai: {$success} prospek berhasil dimasukkan dan otomatis ditugaskan ke {$salesName} sebagai Sales PIC.";

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => $msg,
                'data' => $batch ? $batch->fresh(['user']) : null,
                'success_count' => $success,
                'failed_count' => $failed,
            ], 200);
        }

        return redirect()->route('lead-imports.index')->with('success', $msg);
    }

    public function show(ImportBatch $importBatch)
    {
        $importBatch->load('user');
        $rows = ImportRow::where('import_batch_id', $importBatch->id)
            ->with('lead')->paginate(25);

        return view('imports.show', compact('importBatch', 'rows'));
    }

    protected function parseXlsxFile(string $filePath): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) ($r->t ?? '');
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (! $sheetXml) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $name)) {
                    $sheetXml = $zip->getFromName($name);
                    break;
                }
            }
        }

        $rows = [];
        if ($sheetXml) {
            $xml = simplexml_load_string($sheetXml);
            if ($xml && isset($xml->sheetData->row)) {
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    $currentCol = 0;
                    foreach ($row->c as $cell) {
                        $ref = (string) $cell['r'];
                        if (preg_match('/^([A-Z]+)(\d+)$/', $ref, $matches)) {
                            $letters = $matches[1];
                            $targetCol = 0;
                            for ($k = 0; $k < strlen($letters); $k++) {
                                $targetCol = $targetCol * 26 + (ord($letters[$k]) - 64);
                            }
                            $targetCol--;

                            while ($currentCol < $targetCol) {
                                $rowData[] = '';
                                $currentCol++;
                            }
                        }

                        $type = (string) $cell['t'];
                        $val = '';
                        if ($type === 's') {
                            $idx = (int) $cell->v;
                            $val = $sharedStrings[$idx] ?? '';
                        } elseif ($type === 'inlineStr') {
                            $val = (string) ($cell->is->t ?? '');
                        } else {
                            $val = (string) ($cell->v ?? '');
                        }

                        $rowData[] = trim($val);
                        $currentCol++;
                    }

                    if (! empty(array_filter($rowData, fn ($v) => $v !== ''))) {
                        $rows[] = $rowData;
                    }
                }
            }
        }

        $zip->close();

        return $rows;
    }

    protected function parseCsvFile(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [];
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") {
            fread($handle, 3);
        }

        $delimiter = ',';
        if ($firstLine !== false) {
            $semicolons = substr_count($firstLine, ';');
            $commas = substr_count($firstLine, ',');
            $tabs = substr_count($firstLine, "\t");

            if ($semicolons > $commas && $semicolons > $tabs) {
                $delimiter = ';';
            } elseif ($tabs > $commas && $tabs > $semicolons) {
                $delimiter = "\t";
            }
        }

        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $trimmed = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
            if (! empty(array_filter($trimmed, fn ($v) => $v !== '' && $v !== null))) {
                $rows[] = $trimmed;
            }
        }

        fclose($handle);

        return $rows;
    }

    protected function normalizeHeader(string $header): string
    {
        $h = strtolower(trim($header));
        $h = preg_replace('/[^a-z0-9]/', '', $h);

        if (in_array($h, ['nama', 'namalengkap', 'namapeserta', 'namalead', 'peserta', 'name', 'fullname', 'nama_lengkap'])) {
            return 'name';
        }
        if (in_array($h, ['nik', 'nomornik', 'noktp', 'ktp', 'nomorktp', 'noidentitas', 'identitas'])) {
            return 'nik';
        }
        if (in_array($h, ['tanggallahir', 'tgllahir', 'tgl_lahir', 'birthdate', 'birth_date', 'dob'])) {
            return 'birth_date';
        }
        if (in_array($h, ['whatsapp', 'wa', 'nowa', 'nomorwa', 'nowhatsapp', 'hp', 'nohp', 'nomorhp', 'handphone', 'telepon', 'telp', 'phone', 'notelp', 'no_wa', 'no_hp'])) {
            return 'whatsapp';
        }
        if (in_array($h, ['email', 'surel', 'mail', 'alamatemail', 'e_mail'])) {
            return 'email';
        }
        if (in_array($h, ['perusahaan', 'instansi', 'namainstansi', 'lembaga', 'asalinstansi', 'kantor', 'organisasi', 'company', 'nama_instansi'])) {
            return 'company';
        }
        if (in_array($h, ['jabatan', 'posisi', 'jobtitle', 'pekerjaan', 'profesi', 'job_title'])) {
            return 'job_title';
        }
        if (in_array($h, ['kota', 'city', 'kabupaten', 'kabupatenkota', 'kotakab', 'kota_kabupaten'])) {
            return 'city';
        }
        if (in_array($h, ['provinsi', 'province'])) {
            return 'province';
        }
        if (in_array($h, ['program', 'produk', 'minat', 'pelatihan', 'kursus', 'kelas', 'programminat', 'product', 'program_minat'])) {
            return 'product';
        }
        if (in_array($h, ['catatan', 'notes', 'keterangan', 'ket'])) {
            return 'notes';
        }

        return $h;
    }
}
