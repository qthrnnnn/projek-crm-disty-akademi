<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_reminders', function (Blueprint $table) {
            $table->string('type', 30)->default('follow_up')->after('sales_id'); // follow_up, commission_paid
            $table->foreignId('commission_id')->nullable()->after('lead_id')->constrained('commissions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_reminders', function (Blueprint $table) {
            $table->dropForeign(['commission_id']);
            $table->dropColumn(['type', 'commission_id']);
        });
    }
};
