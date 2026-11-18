<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('follow_up_at');
            $table->string('method', 30)->default('whatsapp'); // whatsapp, call, email, meeting
            $table->string('priority', 20)->default('normal');
            $table->string('status', 30)->default('pending'); // pending, completed, cancelled
            $table->text('result_notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('lead_id');
            $table->index('assigned_to');
            $table->index('follow_up_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
