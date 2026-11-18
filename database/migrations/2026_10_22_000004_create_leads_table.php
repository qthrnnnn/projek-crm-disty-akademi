<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code', 50)->unique();

            // Kontak
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->text('address')->nullable();

            // Relasi
            $table->foreignId('lead_source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();

            // Status: new, contacted, active, negotiation, won, lost, inactive
            $table->string('status', 30)->default('new');

            // Penolakan
            $table->foreignId('rejection_reason_id')->nullable()->constrained('rejection_reasons')->nullOnDelete();
            $table->text('rejection_notes')->nullable();

            // Skoring
            $table->unsignedInteger('lead_score')->default(0);
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent

            // Timestamps bisnis
            $table->dateTime('first_contacted_at')->nullable();
            $table->dateTime('last_contacted_at')->nullable();
            $table->dateTime('converted_at')->nullable();
            $table->dateTime('lost_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('lead_source_id');
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
