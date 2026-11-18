<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sales_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('priority', 20)->default('urgent'); // normal, urgent
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->text('sales_reply')->nullable();
            $table->timestamps();

            $table->index(['sales_id', 'is_completed']);
            $table->index(['sales_id', 'is_read']);
            $table->index('lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_reminders');
    }
};
