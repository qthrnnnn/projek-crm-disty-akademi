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
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name', 50)->nullable()->after('phone');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_name', 150)->nullable()->after('bank_account_number');
        });

        // Set default rekening untuk user sales yang sudah ada
        DB::table('users')->where('email', 'budi@distyakademi.com')->update([
            'bank_name' => 'BCA',
            'bank_account_number' => '883019281',
            'bank_account_name' => 'Budi Santoso',
        ]);

        DB::table('users')->where('email', 'sari@distyakademi.com')->update([
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '141002938192',
            'bank_account_name' => 'Sari Dewi',
        ]);

        DB::table('users')->where('email', 'andi@distyakademi.com')->update([
            'bank_name' => 'BNI',
            'bank_account_number' => '0928371928',
            'bank_account_name' => 'Andi Wijaya',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_name']);
        });
    }
};
