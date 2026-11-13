<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('document_number', 100)->nullable();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('status', 30)->default('active'); // active, inactive, prospect
            $table->string('source', 50)->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('email');
            $table->index('phone');
            $table->index('status');
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
