<?php

use App\Models\Registration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('registrations', 'user_id')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            });
        }

        // Backfill user_id dari lead.assigned_to
        $regs = Registration::with('customer.lead')->get();
        foreach ($regs as $reg) {
            $assignedTo = $reg->customer?->lead?->assigned_to;
            if ($assignedTo) {
                $reg->update(['user_id' => $assignedTo]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('registrations', 'user_id')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
