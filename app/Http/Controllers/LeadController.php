<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Product;
use App\Models\RejectionReason;
use App\Models\User;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $request->get('scope', $user->isSales() ? 'my_leads' : 'all');

        $query = Lead::with(['assignedUser', 'product', 'leadSource', 'latestPendingReminder'])->latest();

        // Scope filter
        if ($scope === 'my_leads' && $user->isSales()) {
            $query->where('assigned_to', $user->id);
        } elseif ($scope === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($scope === 'lost_pool') {
            // Sesuai aturan: jika sales A lost pelanggan ini, sales B boleh meng-followup ulang customer itu
            $query->where('status', 'lost');
            if ($user->isSales()) {
                $query->where('assigned_to', '!=', $user->id);
            }
        } elseif ($scope === 'all') {
            // View all
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($s) => $s
                ->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%")
                ->orWhere('whatsapp', 'like', "%$q%")
                ->orWhere('lead_code', 'like', "%$q%")
                ->orWhere('company', 'like', "%$q%")
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('assigned')) {
            $query->where('assigned_to', $request->assigned);
        }

        $leads = $query->paginate(20)->withQueryString();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();
        $sources = LeadSource::active()->get();

        // Stat counts for tabs
        $myLeadsCount = $user->isSales() ? Lead::where('assigned_to', $user->id)->count() : 0;
        $unassignedCount = Lead::whereNull('assigned_to')->count();
        $lostPoolCount = $user->isSales()
            ? Lead::where('status', 'lost')->where('assigned_to', '!=', $user->id)->count()
            : Lead::where('status', 'lost')->count();
        $totalLeadsCount = Lead::count();

        return view('leads.index', compact(
            'leads', 'salesUsers', 'sources', 'scope',
            'myLeadsCount', 'unassignedCount', 'lostPoolCount', 'totalLeadsCount'
        ));
    }

    public function create()
    {
        $sources = LeadSource::active()->get();
        $products = Product::active()->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('leads.create', compact('sources', 'products', 'salesUsers'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->isAdmin()) {
            return back()->with('error', 'Akses ditolak: Penambahan data lead hanya dapat dilakukan oleh Sales. Akun Admin hanya memiliki akses pemantauan.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'product_id' => 'nullable|exists:products,id',
            'deal_price' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        if (! empty($data['institution']) && empty($data['company'])) {
            $data['company'] = $data['institution'];
        }
        unset($data['institution']);

        // Bersihkan input strip '-'
        if (isset($data['phone']) && in_array(trim($data['phone']), ['-', '--', 'none', 'tidak ada', 'null'])) {
            $data['phone'] = null;
        }
        if (isset($data['whatsapp']) && in_array(trim($data['whatsapp']), ['-', '--', 'none', 'tidak ada', 'null'])) {
            $data['whatsapp'] = null;
        }
        if (isset($data['email']) && in_array(trim($data['email']), ['-', '--', 'none', 'tidak ada', 'null'])) {
            $data['email'] = null;
        }

        if (empty($data['phone']) && ! empty($data['whatsapp'])) {
            $data['phone'] = $data['whatsapp'];
        } elseif (empty($data['whatsapp']) && ! empty($data['phone'])) {
            $data['whatsapp'] = $data['phone'];
        }

        // Aturan: Jika sales yang input, lead otomatis kenalan sales dan ditangani oleh sales tersebut
        if (auth()->user()->isSales()) {
            $data['assigned_to'] = auth()->id();
            if (empty($data['lead_source_id'])) {
                $manualSrc = LeadSource::where('name', 'manual')->first();
                if ($manualSrc) {
                    $data['lead_source_id'] = $manualSrc->id;
                }
            }
        }

        $data['priority'] = 'normal';
        $data['lead_code'] = Lead::generateCode();
        $data['status'] = 'new';
        if (! empty($data['assigned_to'])) {
            $data['assigned_at'] = now();
        }

        $lead = Lead::create($data);

        // Otomatis sinkron instansi jika bukan pribadi
        $inst = $this->syncInstitutionFromCompany($data['company'] ?? null, $data['city'] ?? null, $data['province'] ?? null);

        $msg = 'Calon pelanggan (Lead) berhasil ditambahkan.';
        if (! empty($data['assigned_to'])) {
            $assignedName = $lead->assignedUser?->name ?? 'Sales';
            $msg .= ' Lead resmi ditugaskan secara eksklusif kepada '.$assignedName.'.';
        }
        if ($inst && $inst->wasRecentlyCreated) {
            $msg .= ' Instansi "'.$inst->name.'" otomatis tersimpan ke Daftar Instansi.';
        }

        return redirect()->route('leads.index')->with('success', $msg);
    }

    /**
     * Klaim / Ambil Alih Lead yang Belum Ditugaskan atau Lead yang Lost.
     * Menerapkan aturan:
     * 1. 1 Calon Pelanggan Aktif = 1 Sales Eksklusif.
     * 2. Jika Sales A lost pelanggan tersebut, Sales B boleh mengambil alih untuk follow-up ulang.
     */
    public function claim(Lead $lead)
    {
        $user = auth()->user();
        $previousSalesName = $lead->assignedUser?->name;
        $isReclaimFromLost = $lead->isLost() && $lead->assigned_to && $lead->assigned_to !== $user->id;

        if ($lead->assigned_to && $lead->assigned_to !== $user->id && ! $lead->isLost()) {
            return back()->with('error', 'Akses ditolak: Calon pelanggan ini sedang aktif ditangani oleh '.($previousSalesName ?? 'sales lain').'. Sesuai aturan, satu lead aktif hanya boleh ditangani oleh 1 sales.');
        }

        $lead->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
            'status' => 'contacted', // Diaktifkan kembali untuk di-followup ulang
            'first_contacted_at' => $lead->first_contacted_at ?: now(),
            'lost_at' => null,
            'rejection_reason_id' => null,
            'rejection_notes' => null,
        ]);

        $subject = $isReclaimFromLost
            ? 'Lead berstatus Lost diambil alih oleh Sales: '.$user->name
            : 'Lead diambil alih oleh Sales: '.$user->name;

        $desc = $isReclaimFromLost
            ? 'Sales '.$user->name.' mengambil alih calon pelanggan yang sebelumnya berstatus Lost dari '.($previousSalesName ?? 'sales sebelumnya').' untuk dilakukan follow-up ulang (re-engagement).'
            : 'Sales '.$user->name.' mengklaim tanggung jawab eksklusif untuk menangani calon pelanggan ini.';

        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'status_change',
            'subject' => $subject,
            'description' => $desc,
            'activity_at' => now(),
        ]);

        $msg = $isReclaimFromLost
            ? 'Lead "'.$lead->name.'" yang sebelumnya berstatus Lost berhasil Anda ambil alih. Silakan lakukan follow-up ulang untuk mendapatkan closing!'
            : 'Lead "'.$lead->name.'" berhasil Anda ambil alih. Anda menjadi satu-satunya PIC sales untuk calon pelanggan ini.';

        return back()->with('success', $msg);
    }

    public function show(Lead $lead)
    {
        $lead->load(['assignedUser', 'product', 'leadSource', 'rejectionReason',
            'activities.user', 'followUps.assignedUser', 'payments', 'reminders.admin', 'reminders.sales']);
        $salesUsers = User::whereIn('role', ['sales', 'manager'])->where('is_active', true)->get();
        $rejectionReasons = RejectionReason::active()->get();

        return view('leads.show', compact('lead', 'salesUsers', 'rejectionReasons'));
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();

        // Sales hanya boleh mengedit lead yang ditugaskan kepadanya
        if ($user->isSales() && $lead->assigned_to && $lead->assigned_to !== $user->id) {
            return redirect()->route('leads.show', $lead)->with('error', 'Akses dibatasi: Calon pelanggan ini sedang ditangani oleh '.($lead->assignedUser?->name ?? 'sales lain').'.');
        }

        $sources = LeadSource::active()->get();
        $products = Product::active()->get();
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();

        return view('leads.edit', compact('lead', 'sources', 'products', 'salesUsers'));
    }

    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();

        // Validasi hak akses sales
        if ($user->isSales() && $lead->assigned_to && $lead->assigned_to !== $user->id) {
            return back()->with('error', 'Akses dibatasi: Anda tidak dapat mengubah data lead yang sedang ditangani oleh sales lain.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'product_id' => 'nullable|exists:products,id',
            'deal_price' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        if (! empty($data['institution']) && empty($data['company'])) {
            $data['company'] = $data['institution'];
        }
        unset($data['institution']);

        // Bersihkan input strip '-'
        if (isset($data['phone']) && in_array(trim($data['phone']), ['-', '--', 'none', 'tidak ada', 'null'])) {
            $data['phone'] = null;
        }
        if (isset($data['whatsapp']) && in_array(trim($data['whatsapp']), ['-', '--', 'none', 'tidak ada', 'null'])) {
            $data['whatsapp'] = null;
        }
        if (isset($data['email']) && in_array(trim($data['email']), ['-', '--', 'none', 'tidak ada', 'null'])) {
            $data['email'] = null;
        }

        if (empty($data['phone']) && ! empty($data['whatsapp'])) {
            $data['phone'] = $data['whatsapp'];
        } elseif (empty($data['whatsapp']) && ! empty($data['phone'])) {
            $data['whatsapp'] = $data['phone'];
        }

        // Jika sales, pertahankan assigned_to miliknya
        if ($user->isSales()) {
            $data['assigned_to'] = $lead->assigned_to ?: $user->id;
        }

        $data['priority'] = 'normal';

        if (! empty($data['assigned_to']) && $lead->assigned_to !== (int) $data['assigned_to']) {
            $data['assigned_at'] = now();
        }

        $lead->update($data);

        // Otomatis sinkron instansi jika bukan pribadi
        $this->syncInstitutionFromCompany($data['company'] ?? null, $data['city'] ?? null, $data['province'] ?? null);

        return redirect()->route('leads.show', $lead)->with('success', 'Data lead berhasil diperbarui.');
    }

    public function destroy(Lead $lead)
    {
        $user = auth()->user();
        if ($user->isSales() && $lead->assigned_to !== $user->id) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat menghapus lead yang bukan milik Anda.');
        }

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead berhasil dihapus.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $user = auth()->user();

        if (! $user->isSales()) {
            abort(403, 'Hanya Sales yang dapat memperbarui status lead.');
        }

        // Sales hanya boleh mengubah status lead yang menjadi tanggung jawabnya.
        if ($user->isSales() && $lead->assigned_to && $lead->assigned_to !== $user->id) {
            return back()->with('error', 'Akses ditolak: Calon pelanggan ini sedang ditangani oleh sales lain. Anda tidak diizinkan mengubah statusnya.');
        }

        // Jika lead belum bertuan dan sales ubah status, otomatis klaim lead tersebut
        if ($user->isSales() && ! $lead->assigned_to) {
            $lead->update(['assigned_to' => $user->id, 'assigned_at' => now()]);
        }
        $request->validate([
            'status' => 'required|in:new,contacted,active,negotiation,won,lost,inactive',
            'rejection_reason_id' => 'nullable|exists:rejection_reasons,id',
            'rejection_notes' => 'nullable|string',
        ]);

        $data = ['status' => $request->status];

        // Jika status berpindah dari 'new' ke status apapun (dikontak, aktif, negosiasi, won, lost, inactive),
        // berarti sales sudah merespon / menindaklanjuti lead ini!
        if ($request->status !== 'new') {
            if (! $lead->first_contacted_at) {
                $data['first_contacted_at'] = now();
            }
            $data['last_contacted_at'] = now();
        } else {
            // Jika dikembalikan ke status 'new', reset waktu kontak
            $data['first_contacted_at'] = null;
            $data['last_contacted_at'] = null;
        }

        if ($request->status === 'won') {
            $data['converted_at'] = now();
        }
        if ($request->status === 'lost') {
            $data['lost_at'] = now();
            $data['rejection_reason_id'] = $request->rejection_reason_id;
            $data['rejection_notes'] = $request->rejection_notes;
        }

        $lead->update($data);

        // Jika status menjadi Won, otomatis buat atau hubungkan ke Daftar Customer
        $customerCreated = false;
        if ($request->status === 'won') {
            $this->ensureCustomerCreated($lead);
            $customerCreated = true;

            // Buat komisi 10% untuk Sales dengan status pending (perlu persetujuan admin)
            \App\Models\Commission::ensureCommissionCreated($lead, $lead->deal_price);

            // Otomatis selesaikan follow-up & pengingat pending untuk lead ini
            $lead->followUps()->where('status', 'pending')->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result_notes' => 'Otomatis selesai: Lead Won (Closing)',
            ]);
            \App\Models\SalesReminder::where('lead_id', $lead->id)
                ->where('type', 'follow_up')
                ->where('is_completed', false)
                ->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                    'sales_reply' => 'Selesai: Lead Won',
                ]);
        }

        // Log activity
        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'status_change',
            'subject' => 'Status diubah ke: '.(Lead::STATUS_LABELS[$request->status] ?? $request->status),
            'activity_at' => now(),
        ]);

        $msg = 'Status lead diperbarui.';
        if ($customerCreated) {
            $msg .= ' Data otomatis dimasukkan ke Daftar Customer.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Otomatis membuat atau menghubungkan data Customer saat Lead Won.
     */
    public function ensureCustomerCreated(Lead $lead): Customer
    {
        $existing = Customer::where('lead_id', $lead->id)
            ->orWhere(function ($q) use ($lead) {
                if (! empty($lead->email)) {
                    $q->where('email', $lead->email);
                } elseif (! empty($lead->phone)) {
                    $q->where('phone', $lead->phone);
                }
            })
            ->first();

        if ($existing) {
            $updates = [];
            if (! $existing->lead_id) {
                $updates['lead_id'] = $lead->id;
            }
            if (empty($existing->nik) && ! empty($lead->nik)) {
                $updates['nik'] = $lead->nik;
                $updates['document_number'] = $lead->nik;
            }
            if (empty($existing->birth_date) && ! empty($lead->birth_date)) {
                $updates['birth_date'] = $lead->birth_date;
            }
            if (! empty($updates)) {
                $existing->update($updates);
            }

            return $existing;
        }

        $institutionId = null;
        if (! empty($lead->company)) {
            $inst = $this->syncInstitutionFromCompany($lead->company, $lead->city, $lead->province);
            if ($inst) {
                $institutionId = $inst->id;
            }
        }

        return Customer::create([
            'customer_code' => Customer::generateCode(),
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone ?: ($lead->whatsapp ?: '-'),
            'nik' => $lead->nik,
            'birth_date' => $lead->birth_date,
            'document_number' => $lead->nik,
            'institution_id' => $institutionId,
            'lead_id' => $lead->id,
            'city' => $lead->city,
            'province' => $lead->province,
            'status' => 'active',
            'source' => $lead->leadSource?->name ?? 'crm_won',
            'notes' => 'Otomatis masuk dari Lead '.$lead->lead_code.' ('.($lead->product?->name ?? 'Program').')',
        ]);
    }

    /**
     * Otomatis menyimpan ke Daftar Instansi jika berupa PT / Universitas / Instansi lainnya,
     * dan mengabaikan jika diisi Pribadi / Personal / Individu / Umum.
     */
    public function syncInstitutionFromCompany(?string $companyName, ?string $city = null, ?string $province = null): ?Institution
    {
        if (empty($companyName)) {
            return null;
        }

        $cleanName = trim($companyName);
        $lower = mb_strtolower($cleanName);

        // Abaikan jika pribadi atau bukan instansi
        $ignoreWords = [
            'pribadi', 'personal', 'individu', 'perorangan', 'sendiri',
            'freelance', 'freelancer', 'belum bekerja', 'belum ada',
            'tidak ada', 'none', '-', 'na', 'n/a', 'umum',
        ];

        foreach ($ignoreWords as $word) {
            if ($lower === $word || str_starts_with($lower, $word.' ') || str_ends_with($lower, ' '.$word)) {
                return null;
            }
        }

        // Cek apakah sudah ada di database instansi (case-insensitive)
        $existing = Institution::whereRaw('LOWER(name) = ?', [$lower])
            ->orWhere('name', 'like', $cleanName)
            ->first();

        if ($existing) {
            if (empty($existing->city) && ! empty($city)) {
                $existing->update(['city' => $city, 'province' => $province]);
            }

            return $existing;
        }

        // Klasifikasi cerdas jenis instansi
        $type = 'Swasta / Corporate';
        $industry = 'Bisnis & Layanan';

        if (preg_match('/(universitas|institut|politeknik|sekolah tinggi|kampus|akademi|univ|fakultas|unair|its|ugm|ui|itb|ub|unesa)/i', $cleanName)) {
            $type = 'Perguruan Tinggi';
            $industry = 'Pendidikan';
        } elseif (preg_match('/(smk|sma|smp|sekolah|madrasah|pesantren)/i', $cleanName)) {
            $type = 'Sekolah / SMK';
            $industry = 'Pendidikan Menengah';
        } elseif (preg_match('/(bumn|pln|telkom|pertamina|kai|bank mandiri|bri|bni|btn|pos indonesia)/i', $cleanName)) {
            $type = 'BUMN';
            $industry = 'BUMN / Layanan Publik';
        } elseif (preg_match('/(dinas|kementerian|badan|pemkot|pemkab|pemprov|pemerintah|kelurahan|kecamatan|kantor wilayah|satpol)/i', $cleanName)) {
            $type = 'Pemerintahan';
            $industry = 'Pemerintahan / Birokrasi';
        } elseif (preg_match('/(pt|pt\.|cv|cv\.|persero|tbk|inc|corp|ltd|agency|holding)/i', $cleanName)) {
            $type = 'Swasta / Corporate';
            $industry = 'Teknologi & Bisnis';
        }

        return Institution::create([
            'name' => $cleanName,
            'type' => $type,
            'industry' => $industry,
            'city' => $city,
            'province' => $province,
            'notes' => 'Otomatis tersimpan dari input Prospek / Lead CRM.',
        ]);
    }

    /**
     * Download template CSV untuk import data lead.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_leads.csv"',
        ];

        // Format CSV dengan UTF-8 BOM agar langsung rapi saat dibuka di Microsoft Excel
        $output = "\xEF\xBB\xBF";
        $output .= "Nama,NIK,Tanggal Lahir,WhatsApp,Email,Instansi,Kota,Provinsi,Program,Catatan\n";
        $output .= "Ahmad Fauzi,3578012304950001,1995-04-23,081234567890,ahmad.fauzi@gmail.com,PT Pelindo Daya Sejahtera,Surabaya,Jawa Timur,Pelatihan dan Sertifikasi Digital Filing,Peminat program Digital Filing batch Surabaya\n";
        $output .= "Siti Nurhaliza,3578025508980002,1998-08-15,082198765432,siti.nur@unair.ac.id,Universitas Airlangga,Surabaya,Jawa Timur,Pelatihan Pelayanan Prima,Peningkatan keterampilan pelayanan prima staf\n";
        $output .= "Bambang Prasetyo,3578031201900003,1990-01-12,085712345678,bambang@dinas-jatim.go.id,Dinas Pendidikan Prov Jatim,Surabaya,Jawa Timur,Sertifikasi Pelayanan Prima (Excellent Service) Lisensi BNSP,Mengikuti uji kompetensi sertifikasi BNSP resmi\n";

        return response($output, 200, $headers);
    }

    /**
     * Download template Excel (.xlsx) resmi untuk import data lead.
     */
    public function downloadTemplateExcel()
    {
        $path = public_path('downloads/template_import_leads.xlsx');
        if (! file_exists($path)) {
            $dir = dirname($path);
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return response()->download($path, 'template_import_leads.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Data Leads dari file CSV atau Excel (.xlsx).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'assigned_to' => 'nullable|exists:users,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
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
            return back()->with('error', 'Format file tidak didukung. Harap unggah file dengan format .csv atau .xlsx (Excel).');
        }

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau tidak berisi data yang dapat dibaca.');
        }

        // Ambil baris pertama sebagai header kolom
        $rawHeaders = array_shift($rows);
        $headerMap = [];
        foreach ($rawHeaders as $idx => $colName) {
            $normalized = $this->normalizeHeader((string) $colName);
            if ($normalized) {
                $headerMap[$normalized] = $idx;
            }
        }

        // Default sumber lead
        $defaultSourceId = $request->lead_source_id;
        if (! $defaultSourceId) {
            $source = LeadSource::whereIn('name', ['import_excel', 'import_csv'])->first()
                   ?? LeadSource::first();
            $defaultSourceId = $source?->id;
        }

        // Aturan: Jika sales yang mengimpor, otomatis tugaskan ke sales tersebut
        $assignedTo = auth()->user()->isSales() ? auth()->id() : ($request->assigned_to ?: null);

        $imported = 0;
        $skipped = 0;
        $newInstitutionsCount = 0;

        foreach ($rows as $row) {
            $nameIdx = $headerMap['name'] ?? 0;
            $name = trim($row[$nameIdx] ?? '');

            if (empty($name)) {
                $skipped++;
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

            if ($whatsapp && in_array($whatsapp, ['-', '--', 'none', 'null', 'tidak ada'])) {
                $whatsapp = null;
            }
            if ($phone && in_array($phone, ['-', '--', 'none', 'null', 'tidak ada'])) {
                $phone = null;
            }
            if ($email && in_array($email, ['-', '--', 'none', 'null', 'tidak ada'])) {
                $email = null;
            }

            if (empty($phone) && ! empty($whatsapp)) {
                $phone = $whatsapp;
            } elseif (empty($whatsapp) && ! empty($phone)) {
                $whatsapp = $phone;
            }

            $productId = null;
            if (! empty($prodName)) {
                $product = Product::where('name', 'like', "%{$prodName}%")
                    ->orWhere('code', 'like', "%{$prodName}%")
                    ->first();
                $productId = $product?->id;
            }

            Lead::create([
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
                'lead_source_id' => $defaultSourceId,
                'assigned_to' => $assignedTo,
                'assigned_at' => $assignedTo ? now() : null,
                'status' => 'new',
                'priority' => 'normal',
                'notes' => $notes,
            ]);

            if (! empty($company)) {
                $inst = $this->syncInstitutionFromCompany($company, $city, $province);
                if ($inst && $inst->wasRecentlyCreated) {
                    $newInstitutionsCount++;
                }
            }

            $imported++;
        }

        $msg = "Import selesai: {$imported} data lead berhasil dimasukkan.";
        if ($assignedTo) {
            $msg .= ' Seluruh lead ditugaskan secara eksklusif ke '.User::find($assignedTo)?->name.'.';
        }
        if ($newInstitutionsCount > 0) {
            $msg .= " ({$newInstitutionsCount} instansi baru otomatis tersimpan).";
        }

        return redirect()->route('leads.index')->with('success', $msg);
    }

    protected function parseXlsxFile(string $filePath): array
    {
        $zip = new \ZipArchive();
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
