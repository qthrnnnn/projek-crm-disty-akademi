<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'customer_id',
        'product_id',
        'training_schedule_id',
        'training_date',
        'status',
        'amount',
        'notes',
    ];

    protected $appends = ['product_name', 'schedule_info'];

    protected function casts(): array
    {
        return [
            'training_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class, 'training_schedule_id');
    }

    public function trainingSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class, 'training_schedule_id');
    }

    public function training_schedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class, 'training_schedule_id');
    }

    public function getProductNameAttribute(): ?string
    {
        return $this->product?->name ?? $this->schedule?->product?->name ?? 'Program Pelatihan';
    }

    public function getScheduleInfoAttribute(): ?string
    {
        if (! $this->schedule) {
            return null;
        }
        $date = $this->schedule->training_date?->format('d M Y');
        $trainer = $this->schedule->trainer?->name;
        return $date . ($trainer ? " (Trainer: {$trainer})" : '');
    }

    public static function generateNumber(): string
    {
        $prefix = 'REG-'.now()->format('Ymd');
        $last = static::where('registration_number', 'like', $prefix.'%')
            ->orderByDesc('registration_number')->first();
        $next = $last ? ((int) substr($last->registration_number, -4)) + 1 : 1;

        return $prefix.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
