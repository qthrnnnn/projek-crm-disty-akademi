<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'trainer_id',
        'training_date',
        'start_time',
        'end_time',
        'location',
        'capacity',
        'status',
        'notes',
    ];

    protected $appends = ['product_name', 'trainer_name'];

    protected function casts(): array
    {
        return [
            'training_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getProductNameAttribute(): ?string
    {
        return $this->product?->name ?? 'Program Pelatihan';
    }

    public function getTrainerNameAttribute(): ?string
    {
        return $this->trainer?->name ?? 'Belum ditentukan';
    }
}
