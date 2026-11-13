<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('trainers')->cascadeOnDelete();
            $table->date('training_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->default(20);
            $table->string('status', 30)->default('scheduled'); // scheduled, ongoing, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'training_date']);
            $table->index(['trainer_id', 'training_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_schedules');
    }
};
