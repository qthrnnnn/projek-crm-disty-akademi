<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('commissions', 'bank_name')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->string('bank_name', 50)->nullable()->after('commission_amount');
                $table->string('bank_account_number', 50)->nullable()->after('bank_name');
                $table->string('bank_account_name', 150)->nullable()->after('bank_account_number');
            });
        }

        // Rekapitulasi transaksi historis Budi Santoso saat masih menggunakan BCA (Hendra, Dwi Lestari, Maya)
        DB::table('commissions')
            ->whereIn('id', [1, 2, 3])
            ->update([
                'bank_name' => 'BCA',
                'bank_account_number' => '883019281',
                'bank_account_name' => 'Budi Santoso',
            ]);

        // Transaksi terbaru setelah pergantian rekening ke BNI (Bagus Setiawan)
        DB::table('commissions')
            ->where('id', 4)
            ->update([
                'bank_name' => 'BNI',
                'bank_account_number' => '883019281',
                'bank_account_name' => 'Budi Santoso',
            ]);

        // Untuk komisi lain (jika ada), isi sesuai rekening user saat ini.
        // Instalasi lama/test dapat belum memiliki kolom rekening pada users.
        if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('users', 'bank_name')) {
            DB::table('commissions')
                ->whereNull('commissions.bank_account_number')
                ->join('users', 'commissions.user_id', '=', 'users.id')
                ->update([
                    'commissions.bank_name' => DB::raw('users.bank_name'),
                    'commissions.bank_account_number' => DB::raw('users.bank_account_number'),
                    'commissions.bank_account_name' => DB::raw('COALESCE(users.bank_account_name, users.name)'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_name']);
        });
    }
};
