<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    protected $fillable = [
        'import_batch_id', 'row_number', 'data', 'status', 'errors', 'lead_id',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'data' => 'array',
            'errors' => 'array',
        ];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
