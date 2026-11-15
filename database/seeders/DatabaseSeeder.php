<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Institution;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Registration;
use App\Models\RejectionReason;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (Hanya Admin dan Sales)
        $admin = User::create([
            'name' => 'Admin CRM',
            'email' => 'admin@distyakademi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@distyakademi.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'phone' => '081234567890',
        ]);

        $sari = User::create([
            'name' => 'Sari Dewi',
            'email' => 'sari@distyakademi.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'phone' => '081234567891',
        ]);

        $andi = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@distyakademi.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'phone' => '081234567892',
        ]);

        // 2. Lead Sources
        $sourceManual = LeadSource::create(['name' => 'manual', 'label' => 'Input Manual']);
        $sourceExcel = LeadSource::create(['name' => 'import_excel', 'label' => 'Import Excel']);
        $sourceCsv = LeadSource::create(['name' => 'import_csv', 'label' => 'Import CSV']);
        $sourceWeb = LeadSource::create(['name' => 'website', 'label' => 'Website']);
        $sourceWa = LeadSource::create(['name' => 'whatsapp', 'label' => 'WhatsApp']);

        // 3. Rejection Reasons
        $r1 = RejectionReason::create(['name' => 'too_expensive', 'label' => 'Harga Terlalu Mahal']);
        $r2 = RejectionReason::create(['name' => 'wrong_timing', 'label' => 'Waktu Pelatihan Tidak Cocok']);
        $r3 = RejectionReason::create(['name' => 'no_budget', 'label' => 'Belum Ada Anggaran Kantor']);
        $r4 = RejectionReason::create(['name' => 'competitor', 'label' => 'Memilih Vendor Lain']);
        $r5 = RejectionReason::create(['name' => 'no_response', 'label' => 'Tidak Merespon Chat/Telp']);
        $r6 = RejectionReason::create(['name' => 'wrong_number', 'label' => 'Nomor Salah']);
        $r7 = RejectionReason::create(['name' => 'unreachable', 'label' => 'Tidak Dapat Dihubungi']);
        $r8 = RejectionReason::create(['name' => 'not_willing', 'label' => 'Tidak Bersedia']);
        $r9 = RejectionReason::create(['name' => 'not_interested', 'label' => 'Tidak Tertarik']);

        // 4. Products (Katalog Produk CRM: 3 Program Resmi)
        $pDigitalFiling = Product::create([
            'name' => 'Pelatihan dan Sertifikasi Digital Filing',
            'code' => 'PRD-DF',
            'category' => 'Pelatihan & Sertifikasi',
            'product_type' => 'bundle',
            'price' => 3885000,
            'duration_hours' => 24,
            'description' => 'Pelatihan dan Sertifikasi Digital Filing dirancang untuk membekali peserta dengan keterampilan mengelola, menyimpan, dan menata dokumen secara digital secara efektif, aman, dan efisien sesuai kebutuhan instansi dan organisasi modern. Harga sudah termasuk: Pelatihan Digital Filing, Sertifikat Peserta, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer, Pajak.',
            'is_active' => true,
        ]);

        $pPelayananPrima = Product::create([
            'name' => 'Pelatihan Pelayanan Prima',
            'code' => 'PRD-PP',
            'category' => 'Pelatihan',
            'product_type' => 'training',
            'price' => 1665000,
            'duration_hours' => 16,
            'description' => 'Pelatihan Pelayanan Prima dirancang untuk meningkatkan pengetahuan, keterampilan, dan sikap peserta dalam memberikan pelayanan yang efektif, ramah, profesional, dan berorientasi pada kepuasan pelanggan. Harga sudah termasuk: Pelatihan, Materi Pelatihan, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer.',
            'is_active' => true,
        ]);

        $pSertifikasiBNSP = Product::create([
            'name' => 'Sertifikasi Pelayanan Prima (Excellent Service) Lisensi BNSP',
            'code' => 'BNSP-ES',
            'category' => 'Sertifikasi BNSP',
            'product_type' => 'certification',
            'price' => 888000,
            'duration_hours' => 16,
            'description' => 'Sertifikasi BNSP untuk Skema Pelayanan Prima (Excellent Service) berdasarkan SKKNI Bidang Administratif Profesional. Fasilitas: Harga sudah termasuk pajak, Harga belum termasuk makan, minum dan tempat, Asesmen oleh Asesor yang kompeten sesuai bidangnya, Materi Uji Kompetensi versi 2023, Sertifikat Kompetensi BNSP (bagi yang telah mengikuti asesmen dan direkomendasikan kompeten).',
            'is_active' => true,
        ]);

        // 5. Sample Leads
        // Lead 1: Won with payment & commission
        $lead1 = Lead::create([
            'lead_code' => 'LDA-20261022-0001',
            'name' => 'Dimas Pratama',
            'email' => 'dimas@example.com',
            'phone' => '081299887766',
            'whatsapp' => '081299887766',
            'company' => 'PT Solusi Pratama',
            'job_title' => 'Junior Developer',
            'city' => 'Jakarta Selatan',
            'lead_source_id' => $sourceWeb->id,
            'product_id' => $pDigitalFiling->id,
            'deal_price' => 3885000,
            'assigned_to' => $budi->id,
            'assigned_at' => now()->subDays(2),
            'status' => 'won',
            'priority' => 'high',
            'first_contacted_at' => now()->subDays(2),
            'last_contacted_at' => now()->subDay(),
            'converted_at' => now()->subHours(5),
        ]);

        $payment1 = Payment::create([
            'lead_id' => $lead1->id,
            'product_id' => $pDigitalFiling->id,
            'verified_by' => $admin->id,
            'amount' => 3885000,
            'payment_method' => 'transfer_bank',
            'bank_name' => 'BCA',
            'account_number' => '883019281',
            'account_name' => 'Dimas Pratama',
            'payment_date' => now()->subHours(6),
            'status' => 'valid',
            'verified_at' => now()->subHours(5),
            'verification_notes' => 'Dana mutasi BCA Rp 3.885.000 terverifikasi valid',
        ]);

        Commission::create([
            'user_id' => $budi->id,
            'lead_id' => $lead1->id,
            'payment_id' => $payment1->id,
            'deal_amount' => 3885000,
            'commission_rate' => 10.0,
            'commission_amount' => 388500,
            'status' => 'approved',
            'approved_at' => now()->subHours(4),
        ]);

        // Lead 2: Active / Negotiation (Today)
        $lead2 = Lead::create([
            'lead_code' => 'LDA-20261022-0002',
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nur@perusahaan.co.id',
            'phone' => '085711223344',
            'whatsapp' => '085711223344',
            'company' => 'Bank Mandiri Syariah',
            'job_title' => 'Business Analyst',
            'city' => 'Surabaya',
            'lead_source_id' => $sourceWa->id,
            'product_id' => $pSertifikasiBNSP->id,
            'deal_price' => 888000,
            'assigned_to' => $sari->id,
            'assigned_at' => now()->subHours(4),
            'status' => 'negotiation',
            'priority' => 'urgent',
            'first_contacted_at' => now()->subHours(3),
            'last_contacted_at' => now()->subHour(),
        ]);

        FollowUp::create([
            'lead_id' => $lead2->id,
            'assigned_to' => $sari->id,
            'title' => 'Follow-up proposal penawaran sertifikasi BNSP ke manajemen',
            'follow_up_at' => now()->addHours(2),
            'method' => 'whatsapp',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        Activity::create([
            'lead_id' => $lead2->id,
            'user_id' => $sari->id,
            'type' => 'whatsapp',
            'subject' => 'Kirim skema Sertifikasi BNSP Pelayanan Prima',
            'description' => 'Prospek berminat untuk 3 orang staf pelayanan kantor.',
            'activity_at' => now()->subHours(3),
        ]);

        // Lead 3: New today (unanswered)
        $lead3 = Lead::create([
            'lead_code' => 'LDA-20261022-0003',
            'name' => 'Bambang Irawan',
            'email' => 'bambang@gmail.com',
            'phone' => '081377889900',
            'whatsapp' => '081377889900',
            'company' => 'Universitas Airlangga',
            'job_title' => 'Mahasiswa Akhir',
            'city' => 'Surabaya',
            'lead_source_id' => $sourceWeb->id,
            'product_id' => $pPelayananPrima->id,
            'deal_price' => 1665000,
            'assigned_to' => $andi->id,
            'assigned_at' => now()->subHours(2),
            'status' => 'new',
            'priority' => 'normal',
        ]);

        // Lead 4: Lost / Ditolak
        $lead4 = Lead::create([
            'lead_code' => 'LDA-20261022-0004',
            'name' => 'Hendro Wijoyo',
            'email' => 'hendro@cvprima.id',
            'phone' => '082155667788',
            'whatsapp' => '082155667788',
            'company' => 'CV Prima Perkasa',
            'city' => 'Bandung',
            'lead_source_id' => $sourceManual->id,
            'product_id' => $pPelayananPrima->id,
            'deal_price' => 1665000,
            'assigned_to' => $budi->id,
            'status' => 'lost',
            'rejection_reason_id' => $r1->id,
            'rejection_notes' => 'Anggaran kantor belum mencukupi untuk batch bulan ini',
            'priority' => 'low',
            'first_contacted_at' => now()->subDays(3),
            'lost_at' => now()->subDay(),
        ]);

        // 6. Instansi (Sesuai Gambar 4)
        $instAirlangga = Institution::create([
            'name' => 'Universitas Airlangga',
            'type' => 'Perguruan Tinggi',
            'industry' => 'Pendidikan',
            'email' => 'info@unair.ac.id',
            'phone' => '031-5914042',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'address' => 'Jl. Airlangga No. 4 - 6, Gubeng, Surabaya',
        ]);

        $instTelkom = Institution::create([
            'name' => 'PT Telkom Indonesia (Persero) Tbk',
            'type' => 'BUMN',
            'industry' => 'Telekomunikasi & IT',
            'email' => 'corporate@telkom.co.id',
            'phone' => '021-5215123',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'address' => 'Telkom Landmark Tower, Jl. Gatot Subroto',
        ]);

        $instKominfo = Institution::create([
            'name' => 'Dinas Kominfo Provinsi Jawa Timur',
            'type' => 'Pemerintahan',
            'industry' => 'Pemerintahan / Birokrasi',
            'email' => 'diskominfo@jatimprov.go.id',
            'phone' => '031-8294608',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'address' => 'Jl. Ahmad Yani No. 242-244, Gayungan',
        ]);

        $instMandiri = Institution::create([
            'name' => 'Bank Mandiri Syariah',
            'type' => 'Perbankan',
            'industry' => 'Keuangan Syariah',
            'email' => 'contact@mandirisyariah.co.id',
            'phone' => '031-5452200',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'address' => 'Jl. Basuki Rahmat No. 12-18, Surabaya',
        ]);

        // 7. Customers (Sesuai Gambar 5)
        $custBudi = Customer::create([
            'customer_code' => 'CST-20261022-0001',
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'phone' => '081234567890',
            'document_number' => '3578012345670001',
            'institution_id' => $instAirlangga->id,
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'status' => 'active',
            'source' => 'manual',
            'notes' => 'Dosen Teknik Informatika berminat sertifikasi Laravel React.',
        ]);

        $custSiti = Customer::create([
            'customer_code' => 'CST-20261022-0002',
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nur@perusahaan.co.id',
            'phone' => '085711223344',
            'document_number' => '3174098765430002',
            'institution_id' => $instMandiri->id,
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'status' => 'active',
            'source' => 'manual',
            'notes' => 'Business Analyst mendaftar program Data Science & ML Python.',
        ]);

        $custDimas = Customer::create([
            'customer_code' => 'CST-20261022-0003',
            'name' => 'Dimas Pratama',
            'email' => 'dimas@example.com',
            'phone' => '081299887766',
            'document_number' => '3171011223340003',
            'institution_id' => $instTelkom->id,
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'status' => 'active',
            'source' => 'import_csv',
            'notes' => 'Staff IT Junior yang dibiayai kantor untuk fullstack track.',
        ]);

        $custBambang = Customer::create([
            'customer_code' => 'CST-20261022-0004',
            'name' => 'Bambang Irawan',
            'email' => 'bambang@gmail.com',
            'phone' => '081377889900',
            'document_number' => '3578022334450004',
            'institution_id' => $instAirlangga->id,
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'status' => 'active',
            'source' => 'manual',
            'notes' => 'Calon peserta bootcamp UI/UX batch berikutnya.',
        ]);

        // 8. 15 Trainer Resmi Disty Akademi
        $officialTrainersList = [
            ['name' => 'Drs. Purwanto, M.Si.', 'email' => 'purwanto@distyakademi.com'],
            ['name' => 'Drs. Mochammad Saleh, M.Si.', 'email' => 'saleh@distyakademi.com'],
            ['name' => 'Bayu Pancoroadi, ST.,MT', 'email' => 'bayu@distyakademi.com'],
            ['name' => 'Miftahul Ulum, ST., M.Si.', 'email' => 'miftahul@distyakademi.com'],
            ['name' => 'Mochamad Samsukadi, Lc., M.Th.I', 'email' => 'samsukadi@distyakademi.com'],
            ['name' => 'Galuh Tisna Widiana, M.Pd.', 'email' => 'galuh@distyakademi.com'],
            ['name' => 'M. Yusuf Wibowo, ST.', 'email' => 'yusuf@distyakademi.com'],
            ['name' => 'M. Ali Murtadlo, M.Kom.', 'email' => 'ali@distyakademi.com'],
            ['name' => 'Chandra Sukma Anugrah, M.Kom.', 'email' => 'chandra@distyakademi.com'],
            ['name' => 'Wiwit Denny Fitriana, M.Si.', 'email' => 'wiwit@distyakademi.com'],
            ['name' => 'Dr. Nur Ulwiyah, M.Pd.I', 'email' => 'ulwiyah@distyakademi.com'],
            ['name' => 'Dr. Miftakhul Ilmi S. Putra, M.Pd.', 'email' => 'ilmi@distyakademi.com'],
            ['name' => 'Dr. Dhikrul Hakim, M.Pd.I', 'email' => 'dhikrul@distyakademi.com'],
            ['name' => 'Dr. Moh. Makmun, M.HI', 'email' => 'makmun@distyakademi.com'],
            ['name' => 'Mahmud Huda, M.S.I', 'email' => 'mahmud@distyakademi.com'],
        ];

        $createdTrainers = [];
        foreach ($officialTrainersList as $idx => $tData) {
            $createdTrainers[] = Trainer::firstOrCreate(
                ['name' => $tData['name']],
                [
                    'email' => $tData['email'],
                    'phone' => '08' . str_pad(($idx + 1) * 111222, 10, '0', STR_PAD_RIGHT),
                    'notes' => 'Trainer profesional resmi Disty Akademi',
                    'is_active' => true,
                ]
            );
        }

        $trPurwanto = $createdTrainers[0];
        $trSaleh = $createdTrainers[1];
        $trBayu = $createdTrainers[2];

        // 9. Jadwal Pelatihan (Sesuai Gambar 3)
        $schDF = TrainingSchedule::create([
            'product_id' => $pDigitalFiling->id,
            'trainer_id' => $trPurwanto->id,
            'training_date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
            'location' => 'Disty Lab Komputer A / Live Zoom Online',
            'capacity' => 25,
            'status' => 'scheduled',
            'notes' => 'Siapkan laptop dengan spesifikasi minimal RAM 8GB dan koneksi internet stabil.',
        ]);

        $schBNSP = TrainingSchedule::create([
            'product_id' => $pSertifikasiBNSP->id,
            'trainer_id' => $trSaleh->id,
            'training_date' => now()->addDays(7)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:30:00',
            'location' => 'Disty Academy Training Hall Surabaya',
            'capacity' => 20,
            'status' => 'scheduled',
            'notes' => 'Materi skema Sertifikasi BNSP Pelayanan Prima disediakan sebelum uji kompetensi.',
        ]);

        $schPP = TrainingSchedule::create([
            'product_id' => $pPelayananPrima->id,
            'trainer_id' => $trBayu->id,
            'training_date' => now()->addDays(12)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '16:00:00',
            'location' => 'Zoom Meeting VIP (Interactive)',
            'capacity' => 30,
            'status' => 'scheduled',
            'notes' => 'Modul panduan service excellent digital dibagikan pada hari pertama pelatihan.',
        ]);

        // 10. Pendaftaran Peserta (Sesuai Gambar 1)
        // Rule: Customer didaftarkan ke jadwal yang telah dibuat
        Registration::create([
            'registration_number' => 'REG-20261022-0001',
            'customer_id' => $custDimas->id,
            'training_schedule_id' => $schDF->id,
            'product_id' => $pDigitalFiling->id,
            'training_date' => $schDF->training_date,
            'amount' => 3885000,
            'status' => 'confirmed',
            'notes' => 'Pembayaran lunas via transfer BCA.',
        ]);

        Registration::create([
            'registration_number' => 'REG-20261022-0002',
            'customer_id' => $custSiti->id,
            'training_schedule_id' => $schBNSP->id,
            'product_id' => $pSertifikasiBNSP->id,
            'training_date' => $schBNSP->training_date,
            'amount' => 888000,
            'status' => 'registered',
            'notes' => 'Menunggu verifikasi transfer dari finance kantor.',
        ]);

        $this->call(CompanyProfileSeeder::class);
        $this->call(CommissionSeeder::class);
    }
}
