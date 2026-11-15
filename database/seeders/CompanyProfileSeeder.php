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
            'Bayu Pancoroadi, ST.,MT',
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

        // Sesuai arahan, katalog produk difokuskan pada 3 program utama resmi (PRD-DF, PRD-PP, BNSP-ES)
    }
}
