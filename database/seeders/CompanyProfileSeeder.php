<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Trainer;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        $trainerNames = [
            'Drs. Purwanto, M.Si.',
            'Drs. Mochammad Saleh, M.Si.',
            'Bayu Pancoroadi, ST., MT',
            'Miftahul Ulum, ST., M.Si.',
            'Mochamad Samsukadi, Lc., M.Th.I',
            'Galuh Tisna Widiana, M.Pd.',
            'M. Yusuf Wibowo, ST.',
            'M. Ali Murtadlo, M.Kom.',
            'Chandra Sukma Anugrah, M.Kom.',
            'Wiwit Denny Fitriana, M.Si.',
            'Dr. Nur Ulwiyah, M.Pd.I',
            'Dr. Miftakhul Ilmi S. Putra, M.Pd.',
            'Dr. Dhikrul Hakim, M.Pd.I',
            'Dr. Moh. Makmun, M.HI',
            'Mahmud Huda, M.S.I',
        ];

        foreach ($trainerNames as $name) {
            Trainer::firstOrCreate(
                ['name' => $name],
                [
                    'notes' => 'Trainer profesional tersertifikasi BNSP Republik Indonesia. Koordinator trainer: Drs. Purwanto, M.Si.',
                    'is_active' => true,
                ],
            );
        }

        Trainer::query()
            ->where(function ($query) {
                $query->whereNull('notes')
                    ->orWhere('notes', 'not like', '%BNSP Republik Indonesia%');
            })
            ->each(function (Trainer $trainer) {
                $trainer->update([
                    'notes' => trim(($trainer->notes ? $trainer->notes.' ' : '').'Trainer profesional tersertifikasi BNSP Republik Indonesia.'),
                ]);
            });

        $programs = [
            ['name' => 'Desain Grafis', 'code' => 'BNSP-DG', 'description' => 'Prinsip desain, Photoshop, Corel Draw, InDesign, dan Canva. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP terkait.'],
            ['name' => 'Public Speaking', 'code' => 'BNSP-PS', 'description' => 'Pelatihan public speaking dan komunikasi profesional. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP terkait.'],
            ['name' => 'Kepala Administrasi Proyek', 'code' => 'BNSP-KAP', 'description' => 'Pelatihan administrasi proyek berbasis kompetensi SKKNI. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP Administrasi Bisnis Perkantoran Modern.'],
            ['name' => 'Sekretaris', 'code' => 'BNSP-SEK', 'description' => 'Pelatihan kompetensi sekretaris berbasis SKKNI. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP Administrasi Bisnis Perkantoran Modern.'],
            ['name' => 'Tata Usaha', 'code' => 'BNSP-TU', 'description' => 'Pelatihan tata usaha berbasis kompetensi SKKNI. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP Administrasi Bisnis Perkantoran Modern.'],
            ['name' => 'Staf Administrasi', 'code' => 'BNSP-SA', 'description' => 'Pelatihan staf administrasi berbasis kompetensi SKKNI. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP Administrasi Bisnis Perkantoran Modern.'],
            ['name' => 'Sekuriti', 'code' => 'BNSP-SKR', 'description' => 'Keamanan fisik, keamanan informasi, dan manajemen risiko. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP terkait.'],
            ['name' => 'K3 Umum', 'code' => 'BNSP-K3', 'description' => 'Keselamatan dan Kesehatan Kerja umum berbasis kompetensi. Sertifikat teknis serta sertifikat kompetensi BNSP RI difasilitasi melalui LSP terkait.'],
            ['name' => 'Tata Busana', 'code' => 'KHUSUS-TB', 'description' => 'Pelatihan teknis spesifik sesuai kebutuhan klien. Sertifikasi dapat difasilitasi melalui LSP rekanan sesuai bidang kompetensi.'],
            ['name' => 'Tata Rias', 'code' => 'KHUSUS-TR', 'description' => 'Pelatihan teknis spesifik sesuai kebutuhan klien. Sertifikasi dapat difasilitasi melalui LSP rekanan sesuai bidang kompetensi.'],
        ];

        foreach ($programs as $program) {
            Product::firstOrCreate(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'category' => 'Pelatihan & Sertifikasi BNSP',
                    'product_type' => 'training',
                    'price' => 0,
                    'description' => $program['description'],
                    'is_active' => true,
                ],
            );
        }

        $certifications = [
            ['name' => 'Sertifikasi Kompetensi Instruktur KKNI Level 4', 'code' => 'CERT-IKN4', 'description' => 'Uji asesmen kompetensi instruktur/trainer KKNI Level 4 melalui LSP Instruktur Kompetensi Nasional (LSP IKN).'],
            ['name' => 'Sertifikasi Kompetensi Instruktur KKNI Level 3', 'code' => 'CERT-IKN3', 'description' => 'Uji asesmen kompetensi instruktur/trainer KKNI Level 3 melalui LSP Instruktur Kompetensi Nasional (LSP IKN).'],
            ['name' => 'Sertifikasi Kompetensi Administrasi Perkantoran', 'code' => 'CERT-ABP', 'description' => 'Uji asesmen kompetensi administrasi, sekretaris, dan administrasi proyek melalui LSP Administrasi Bisnis Perkantoran Modern.'],
        ];

        foreach ($certifications as $certification) {
            Product::firstOrCreate(
                ['code' => $certification['code']],
                [
                    'name' => $certification['name'],
                    'category' => 'Sertifikasi BNSP',
                    'product_type' => 'certification',
                    'price' => 0,
                    'description' => $certification['description'],
                    'is_active' => true,
                ],
            );
        }

        Product::firstOrCreate(
            ['code' => 'BUNDLE-TOT'],
            [
                'name' => 'Paket Pelatihan + Sertifikasi ToT',
                'category' => 'Paket Bundling',
                'product_type' => 'bundle',
                'price' => 0,
                'duration_hours' => 56,
                'description' => 'Kelas pembekalan ToT KKNI Level 3 atau Level 4 yang dilanjutkan uji kompetensi BNSP melalui LSP IKN.',
                'is_active' => true,
            ],
        );

        Product::query()
            ->whereIn('name', [
                'ToT KKNI Level 4',
                'ToT KKNI Level 3',
                'Digital Marketing',
                'Operator Komputer',
                'Soft Skills',
                'Service Excellent',
            ])
            ->update([
                'product_type' => 'training',
                'category' => 'Pelatihan / Kelas Belajar',
            ]);

        Product::query()
            ->whereIn('name', [
                'ToT KKNI Level 4',
                'ToT KKNI Level 3',
                'Digital Marketing',
                'Operator Komputer',
                'Soft Skills',
                'Service Excellent',
            ])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', 'not like', '%BNSP Republik Indonesia%');
            })
            ->each(function (Product $product) {
                $product->update([
                    'description' => trim(($product->description ? $product->description.' ' : '').'Peserta yang lulus mendapatkan sertifikat teknis serta sertifikat kompetensi resmi BNSP Republik Indonesia, dengan fasilitasi LSP terkait.'),
                ]);
            });
    }
}
