<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('institutions')->select(['id', 'name', 'type'])->chunkById(200, function ($institutions) {
            foreach ($institutions as $institution) {
                if (preg_match('/\b(universitas|institut|politeknik)\b/iu', $institution->name)
                    && $institution->type !== 'Perguruan Tinggi') {
                    DB::table('institutions')->where('id', $institution->id)
                        ->update(['type' => 'Perguruan Tinggi']);
                }
            }
        });
    }

    public function down(): void
    {
        // Data correction: the previous (potentially incorrect) type cannot be inferred.
    }
};
