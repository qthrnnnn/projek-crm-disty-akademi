<?php

namespace Database\Seeders;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommissionSeeder extends Seeder
{
    public function run(): void
    {
        $budi = User::where('email', 'budi@distyakademi.com')->first();
        $sari = User::where('email', 'sari@distyakademi.com')->first();

        if (! $budi || ! $sari) {
            return;
        }

        $prodDF = Product::where('code', 'PRD-DF')->first();
        $prodPP = Product::where('code', 'PRD-PP')->first();
        $prodBNSP = Product::where('code', 'BNSP-ES')->first();

        if (! $prodDF || ! $prodPP || ! $prodBNSP) {
            return;
        }

        // 1. Budi Santoso - Paid (Digital Filing Rp 3.885.000, komisi Rp 388.500)
        $leadSyakira = Lead::where('name', 'like', '%Syakira%')->first();
        if ($leadSyakira) {
            $leadSyakira->update([
                'product_id' => $prodDF->id,
                'deal_price' => $prodDF->price,
                'assigned_to' => $budi->id,
                'status' => 'won',
                'converted_at' => now()->subDays(3),
            ]);
            Commission::firstOrCreate(
                ['lead_id' => $leadSyakira->id],
                [
                    'user_id' => $budi->id,
                    'deal_amount' => $prodDF->price,
                    'commission_rate' => 10.0,
                    'commission_amount' => $prodDF->price * 0.10,
                    'status' => 'paid',
                    'approved_at' => now()->subDays(3),
                    'paid_at' => now()->subDays(2),
                    'notes' => 'Komisi 10% lunas ditransfer ke rekening BCA Budi Santoso.',
                ]
            );
        }

        // 2. Budi Santoso - Approved (Sertifikasi BNSP Rp 888.000, komisi Rp 88.800)
        $leadJongu = Lead::where('name', 'jongu')->first();
        if ($leadJongu) {
            $leadJongu->update([
                'product_id' => $prodBNSP->id,
                'deal_price' => $prodBNSP->price,
                'assigned_to' => $budi->id,
                'status' => 'won',
                'converted_at' => now()->subDays(1),
            ]);
            Commission::firstOrCreate(
                ['lead_id' => $leadJongu->id],
                [
                    'user_id' => $budi->id,
                    'deal_amount' => $prodBNSP->price,
                    'commission_rate' => 10.0,
                    'commission_amount' => $prodBNSP->price * 0.10,
                    'status' => 'approved',
                    'approved_at' => now()->subHours(5),
                    'notes' => 'Telah disetujui Admin, siap dicairkan pada jadwal transfer mingguan.',
                ]
            );
        }

        // 3. Budi Santoso - Pending (Digital Filing Rp 3.885.000, komisi Rp 388.500)
        $leadRuto = Lead::where('name', 'ruto')->first();
        if ($leadRuto) {
            $leadRuto->update([
                'product_id' => $prodDF->id,
                'deal_price' => $prodDF->price,
                'assigned_to' => $budi->id,
                'status' => 'won',
                'converted_at' => now()->subHours(2),
            ]);
            Commission::firstOrCreate(
                ['lead_id' => $leadRuto->id],
                [
                    'user_id' => $budi->id,
                    'deal_amount' => $prodDF->price,
                    'commission_rate' => 10.0,
                    'commission_amount' => $prodDF->price * 0.10,
                    'status' => 'pending',
                    'notes' => 'Closing pembayaran berhasil, menunggu verifikasi persetujuan admin.',
                ]
            );
        }

        // 4. Sari Dewi - Paid (Sertifikasi BNSP Rp 888.000, komisi Rp 88.800)
        $leadNadifa = Lead::where('name', 'nadifa')->first();
        if ($leadNadifa) {
            $leadNadifa->update([
                'product_id' => $prodBNSP->id,
                'deal_price' => $prodBNSP->price,
                'assigned_to' => $sari->id,
                'status' => 'won',
                'converted_at' => now()->subDays(4),
            ]);
            Commission::firstOrCreate(
                ['lead_id' => $leadNadifa->id],
                [
                    'user_id' => $sari->id,
                    'deal_amount' => $prodBNSP->price,
                    'commission_rate' => 10.0,
                    'commission_amount' => $prodBNSP->price * 0.10,
                    'status' => 'paid',
                    'approved_at' => now()->subDays(4),
                    'paid_at' => now()->subDays(3),
                    'notes' => 'Komisi 10% lunas ditransfer ke rekening Mandiri Sari Dewi.',
                ]
            );
        }

        // 5. Sari Dewi - Approved (Pelayanan Prima Rp 1.665.000, komisi Rp 166.500)
        $leadTuti = Lead::where('name', 'tuti')->first();
        if ($leadTuti) {
            $leadTuti->update([
                'product_id' => $prodPP->id,
                'deal_price' => $prodPP->price,
                'assigned_to' => $sari->id,
                'status' => 'won',
                'converted_at' => now()->subDays(1),
            ]);
            Commission::firstOrCreate(
                ['lead_id' => $leadTuti->id],
                [
                    'user_id' => $sari->id,
                    'deal_amount' => $prodPP->price,
                    'commission_rate' => 10.0,
                    'commission_amount' => $prodPP->price * 0.10,
                    'status' => 'approved',
                    'approved_at' => now()->subHours(3),
                    'notes' => 'Telah disetujui Admin, siap dicairkan pada jadwal transfer mingguan.',
                ]
            );
        }

        // 6. Sari Dewi - Pending (Digital Filing Rp 3.885.000, komisi Rp 388.500)
        $leadSiti = Lead::where('name', 'Siti Nurhaliza')->first();
        if ($leadSiti) {
            $leadSiti->update([
                'product_id' => $prodDF->id,
                'deal_price' => $prodDF->price,
                'assigned_to' => $sari->id,
                'status' => 'won',
                'converted_at' => now()->subHours(1),
            ]);
            Commission::firstOrCreate(
                ['lead_id' => $leadSiti->id],
                [
                    'user_id' => $sari->id,
                    'deal_amount' => $prodDF->price,
                    'commission_rate' => 10.0,
                    'commission_amount' => $prodDF->price * 0.10,
                    'status' => 'pending',
                    'notes' => 'Closing pembayaran berhasil, menunggu verifikasi persetujuan admin.',
                ]
            );
        }
    }
}
