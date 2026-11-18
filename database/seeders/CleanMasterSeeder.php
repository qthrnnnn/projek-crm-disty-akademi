<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\LeadSource;
use App\Models\Product;
use App\Models\RejectionReason;
use App\Models\Trainer;
use App\Models\TrainingSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CleanMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (Hanya Admin CRM dan 3 Sales Resmi)
        $admin = User::firstOrCreate(
            ['email' => 'admin@distyakademi.com'],
            [
                'name' => 'Admin CRM',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567800',
                'is_active' => true,
            ]
        );

        $budi = User::firstOrCreate(
            ['email' => 'budi@distyakademi.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'sales',
                'phone' => '081234567890',
                'bank_name' => 'BNI',
                'bank_account_number' => '883019281',
                'bank_account_name' => 'Budi Santoso',
                'is_active' => true,
            ]
        );

        $sari = User::firstOrCreate(
            ['email' => 'sari@distyakademi.com'],
            [
                'name' => 'Sari Dewi',
                'password' => Hash::make('password'),
                'role' => 'sales',
                'phone' => '081234567891',
                'bank_name' => 'BCA',
                'bank_account_number' => '772019281',
                'bank_account_name' => 'Sari Dewi',
                'is_active' => true,
            ]
        );

        $andi = User::firstOrCreate(
            ['email' => 'andi@distyakademi.com'],
            [
                'name' => 'Andi Wijaya',
                'password' => Hash::make('password'),
                'role' => 'sales',
                'phone' => '081234567892',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '1420019281',
                'bank_account_name' => 'Andi Wijaya',
                'is_active' => true,
            ]
        );

        // 2. Lead Sources (Sumber Prospek)
        LeadSource::firstOrCreate(['name' => 'manual'], ['label' => 'Input Manual']);
        LeadSource::firstOrCreate(['name' => 'import_excel'], ['label' => 'Import Excel']);
        LeadSource::firstOrCreate(['name' => 'import_csv'], ['label' => 'Import CSV']);
        LeadSource::firstOrCreate(['name' => 'website'], ['label' => 'Website']);
        LeadSource::firstOrCreate(['name' => 'whatsapp'], ['label' => 'WhatsApp']);

        // 3. Rejection Reasons (9 Alasan Penolakan SOP)
        RejectionReason::firstOrCreate(['name' => 'too_expensive'], ['label' => 'Harga Terlalu Mahal']);
        RejectionReason::firstOrCreate(['name' => 'wrong_timing'], ['label' => 'Waktu Pelatihan Tidak Cocok']);
        RejectionReason::firstOrCreate(['name' => 'no_budget'], ['label' => 'Belum Ada Anggaran Kantor']);
        RejectionReason::firstOrCreate(['name' => 'competitor'], ['label' => 'Memilih Vendor Lain']);
        RejectionReason::firstOrCreate(['name' => 'no_response'], ['label' => 'Tidak Merespon Chat/Telp']);
        RejectionReason::firstOrCreate(['name' => 'wrong_number'], ['label' => 'Nomor Salah']);
        RejectionReason::firstOrCreate(['name' => 'unreachable'], ['label' => 'Tidak Dapat Dihubungi']);
        RejectionReason::firstOrCreate(['name' => 'not_willing'], ['label' => 'Tidak Bersedia']);
        RejectionReason::firstOrCreate(['name' => 'not_interested'], ['label' => 'Tidak Tertarik']);

        // 4. Products (3 Program Pelatihan Resmi)
        $pDigitalFiling = Product::firstOrCreate(
            ['code' => 'PRD-DF'],
            [
                'name' => 'Pelatihan dan Sertifikasi Digital Filing',
                'category' => 'Pelatihan & Sertifikasi',
                'product_type' => 'bundle',
                'price' => 3885000,
                'duration_hours' => 24,
                'description' => 'Pelatihan dan Sertifikasi Digital Filing dirancang untuk membekali peserta dengan keterampilan mengelola, menyimpan, dan menata dokumen secara digital secara efektif, aman, dan efisien sesuai kebutuhan instansi dan organisasi modern. Harga sudah termasuk: Pelatihan Digital Filing, Sertifikat Peserta, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer, Pajak.',
                'is_active' => true,
            ]
        );

        $pPelayananPrima = Product::firstOrCreate(
            ['code' => 'PRD-PP'],
            [
                'name' => 'Pelatihan Pelayanan Prima',
                'category' => 'Pelatihan',
                'product_type' => 'training',
                'price' => 1665000,
                'duration_hours' => 16,
                'description' => 'Pelatihan Pelayanan Prima dirancang untuk meningkatkan pengetahuan, keterampilan, dan sikap peserta dalam memberikan pelayanan yang efektif, ramah, profesional, dan berorientasi pada kepuasan pelanggan. Harga sudah termasuk: Pelatihan, Materi Pelatihan, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer.',
                'is_active' => true,
            ]
        );

        $pSertifikasiBNSP = Product::firstOrCreate(
            ['code' => 'BNSP-ES'],
            [
                'name' => 'Sertifikasi Pelayanan Prima (Excellent Service) Lisensi BNSP',
                'category' => 'Sertifikasi BNSP',
                'product_type' => 'certification',
                'price' => 888000,
                'duration_hours' => 16,
                'description' => 'Sertifikasi BNSP untuk Skema Pelayanan Prima (Excellent Service) berdasarkan SKKNI Bidang Administratif Profesional. Fasilitas: Harga sudah termasuk pajak, Asesmen oleh Asesor kompeten, Materi Uji Kompetensi, Sertifikat Kompetensi BNSP resmi.',
                'is_active' => true,
            ]
        );

        // 5. Instansi / Mitra Perusahaan & Kedinasan (Kosongan - Dibuat mandiri oleh Admin/Sales saat operasional)
        // Tidak ada data default instansi yang di-seed agar daftar instansi bersih total (0 data).

        // 6. 15 Trainer Resmi Disty Akademi
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


    }
}
