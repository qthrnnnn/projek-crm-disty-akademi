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

        // 4. Products (Pelatihan / Bootcamp & Sertifikasi BNSP)
        // Kategori: Pelatihan / Bootcamp
        $pAWS = Product::create([
            'name' => 'Cloud Computing with AWS',
            'code' => 'PRD-AWS',
            'category' => 'Pelatihan / Bootcamp',
            'price' => 150000,
            'duration_hours' => 24,
            'description' => 'Intensive Live Class bersama Experts. Praktikal & Mendalam. Benefit: Gratis E-Certificate, Live Class dan Q&A bersama mentor profesional, Case Study, Praktik, Tes dan membangun Portofolio, Join community secara lifetime.',
        ]);

        $pUI = Product::create([
            'name' => 'UI/UX Research and Design',
            'code' => 'PRD-UIUX',
            'category' => 'Pelatihan / Bootcamp',
            'price' => 0,
            'duration_hours' => 16,
            'description' => 'Intensive Live Class bersama Experts. Praktikal & Mendalam. Benefit: Gratis E-Certificate, Live Class dan Q&A bersama mentor profesional, Case Study, Praktik, Tes dan membangun Portofolio, Join community secara lifetime.',
        ]);

        $pWeb = Product::create([
            'name' => 'Fullstack Web Development',
            'code' => 'PRD-WEB',
            'category' => 'Pelatihan / Bootcamp',
            'price' => 1500000,
            'duration_hours' => 48,
            'description' => 'Web Programming Laravel & React. Benefit: Gratis E-Certificate, Live Class dan Q&A bersama mentor profesional, Case Study, Praktik, Tes dan membangun Portofolio, Join community secara lifetime.',
        ]);

        // Kategori: Sertifikasi BNSP
        $pTot4 = Product::create([
            'name' => 'ToT KKNI Level 4',
            'code' => 'BNSP-TOT4',
            'category' => 'Sertifikasi BNSP',
            'price' => 0,
            'duration_hours' => 32,
            'description' => 'Program resmi uji kompetensi sesuai standar nasional ToT KKNI Level 4 Sertifikasi BNSP. Materi intensif, simulasi & persiapan uji kompetensi, sertifikat resmi BNSP.',
        ]);

        $pTot3 = Product::create([
            'name' => 'ToT KKNI Level 3',
            'code' => 'BNSP-TOT3',
            'category' => 'Sertifikasi BNSP',
            'price' => 500000,
            'duration_hours' => 24,
            'description' => 'Program resmi uji kompetensi sesuai standar nasional ToT KKNI Level 3 Sertifikasi BNSP. Materi intensif, simulasi & persiapan uji kompetensi, sertifikat resmi BNSP.',
        ]);

        $pDM = Product::create([
            'name' => 'Digital Marketing',
            'code' => 'BNSP-DM',
            'category' => 'Sertifikasi BNSP',
            'price' => 0,
            'duration_hours' => 20,
            'description' => 'Program resmi uji kompetensi sesuai standar nasional Digital Marketing Sertifikasi BNSP. Materi intensif, simulasi & persiapan uji kompetensi, sertifikat resmi BNSP.',
        ]);

        $pOK = Product::create([
            'name' => 'Operator Komputer',
            'code' => 'BNSP-OK',
            'category' => 'Sertifikasi BNSP',
            'price' => 1200000,
            'duration_hours' => 30,
            'description' => 'Program resmi uji kompetensi sesuai standar nasional Operator Komputer Sertifikasi BNSP. Materi intensif, simulasi & persiapan uji kompetensi, sertifikat resmi BNSP.',
        ]);

        $pSS = Product::create([
            'name' => 'Soft Skills',
            'code' => 'BNSP-SS',
            'category' => 'Sertifikasi BNSP',
            'price' => 0,
            'duration_hours' => 16,
            'description' => 'Program resmi uji kompetensi sesuai standar nasional Soft Skills Sertifikasi BNSP. Materi intensif, simulasi & persiapan uji kompetensi, sertifikat resmi BNSP.',
        ]);

        $pSE = Product::create([
            'name' => 'Service Excellent',
            'code' => 'BNSP-SE',
            'category' => 'Sertifikasi BNSP',
            'price' => 0,
            'duration_hours' => 16,
            'description' => 'Program resmi uji kompetensi sesuai standar nasional Service Excellent Sertifikasi BNSP. Materi intensif, simulasi & persiapan uji kompetensi, sertifikat resmi BNSP.',
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
            'product_id' => $pWeb->id,
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
            'product_id' => $pWeb->id,
            'verified_by' => $admin->id,
            'amount' => 1500000,
            'payment_method' => 'transfer_bank',
            'bank_name' => 'BCA',
            'account_number' => '883019281',
            'account_name' => 'Dimas Pratama',
            'payment_date' => now()->subHours(6),
            'status' => 'valid',
            'verified_at' => now()->subHours(5),
            'verification_notes' => 'Dana mutasi BCA Rp 1.500.000 terverifikasi valid',
        ]);

        Commission::create([
            'user_id' => $budi->id,
            'lead_id' => $lead1->id,
            'payment_id' => $payment1->id,
            'deal_amount' => 1500000,
            'commission_rate' => 10.0,
            'commission_amount' => 150000,
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
            'product_id' => $pTot3->id,
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
            'subject' => 'Kirim skema ToT KKNI Level 3 & diskon kelompok',
            'description' => 'Prospek berminat untuk 3 orang trainer internal kantor.',
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
            'product_id' => $pUI->id,
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
            'product_id' => $pOK->id,
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

        // 8. Trainers (Sesuai Gambar 2)
        $trEko = Trainer::create([
            'name' => 'Dr. Ir. Eko Prasetyo, M.Kom',
            'email' => 'eko.prasetyo@distyakademi.com',
            'phone' => '081122334455',
            'notes' => 'Senior Web Architecture & Lead Technical Trainer',
            'is_active' => true,
        ]);

        $trRina = Trainer::create([
            'name' => 'Rina Wulandari, S.Kom, M.Sc',
            'email' => 'rina.wulandari@distyakademi.com',
            'phone' => '081133445566',
            'notes' => 'Data Scientist & AI Specialist with 8+ years experience',
            'is_active' => true,
        ]);

        $trFajar = Trainer::create([
            'name' => 'Fajar Hidayat, S.Ds',
            'email' => 'fajar.hidayat@distyakademi.com',
            'phone' => '081144556677',
            'notes' => 'Principal UI/UX Designer & Design Sprint Master',
            'is_active' => true,
        ]);

        // 9. Jadwal Pelatihan (Sesuai Gambar 3)
        $schWeb = TrainingSchedule::create([
            'product_id' => $pWeb->id,
            'trainer_id' => $trEko->id,
            'training_date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
            'location' => 'Disty Lab Komputer A / Live Zoom Online',
            'capacity' => 25,
            'status' => 'scheduled',
            'notes' => 'Siapkan laptop dengan spesifikasi minimal RAM 8GB dan Node.js / PHP 8.2 terinstal.',
        ]);

        $schTot3 = TrainingSchedule::create([
            'product_id' => $pTot3->id,
            'trainer_id' => $trRina->id,
            'training_date' => now()->addDays(7)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:30:00',
            'location' => 'Disty Academy Training Hall Surabaya',
            'capacity' => 20,
            'status' => 'scheduled',
            'notes' => 'Materi skema sertifikasi ToT KKNI Level 3 & panduan asesmen disediakan sebelum uji kompetensi.',
        ]);

        $schUI = TrainingSchedule::create([
            'product_id' => $pUI->id,
            'trainer_id' => $trFajar->id,
            'training_date' => now()->addDays(12)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '16:00:00',
            'location' => 'Zoom Meeting VIP (Interactive)',
            'capacity' => 30,
            'status' => 'scheduled',
            'notes' => 'Akun Figma profesional gratis selama durasi program.',
        ]);

        // 10. Pendaftaran Peserta (Sesuai Gambar 1)
        // Rule: Customer didaftarkan ke jadwal yang telah dibuat
        Registration::create([
            'registration_number' => 'REG-20261022-0001',
            'customer_id' => $custDimas->id,
            'training_schedule_id' => $schWeb->id,
            'product_id' => $pWeb->id,
            'training_date' => $schWeb->training_date,
            'amount' => 1500000,
            'status' => 'confirmed',
            'notes' => 'Pembayaran lunas via transfer BCA.',
        ]);

        Registration::create([
            'registration_number' => 'REG-20261022-0002',
            'customer_id' => $custSiti->id,
            'training_schedule_id' => $schTot3->id,
            'product_id' => $pTot3->id,
            'training_date' => $schTot3->training_date,
            'amount' => 500000,
            'status' => 'registered',
            'notes' => 'Menunggu verifikasi transfer dari finance kantor.',
        ]);

        $this->call(CompanyProfileSeeder::class);
    }
}
