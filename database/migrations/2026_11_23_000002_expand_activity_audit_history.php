<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->unsignedBigInteger('lead_id')->nullable()->change();
            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
            $table->string('entity_type', 60)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('lead_name')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->json('changes')->nullable();
            $table->index(['entity_type', 'entity_id']);
        });

        DB::table('activities')->orderBy('id')->chunkById(200, function ($activities) {
            foreach ($activities as $activity) {
                $lead = DB::table('leads')->where('id', $activity->lead_id)->first();
                DB::table('activities')->where('id', $activity->id)->update([
                    'lead_name' => $lead?->name,
                    'owner_id' => $lead?->assigned_to,
                    'actor_name' => DB::table('users')->where('id', $activity->user_id)->value('name'),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Keep nullable lead links and null-on-delete so history is not destroyed.
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['entity_type', 'entity_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn(['entity_type', 'entity_id', 'entity_name', 'actor_name', 'lead_name', 'owner_id', 'changes']);
        });
    }
};
