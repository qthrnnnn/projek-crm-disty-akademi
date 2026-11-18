<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'description', 'category', 'product_type',
        'price', 'duration_hours', 'is_active',
    ];

    protected $appends = ['product_type_label'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function trainingSchedules(): HasMany
    {
        return $this->hasMany(TrainingSchedule::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function productTypeLabel(): string
    {
        return match ($this->product_type) {
            'certification' => 'Uji Sertifikasi BNSP',
            'bundle' => 'Pelatihan + Sertifikasi BNSP',
            default => 'Pelatihan / Kelas',
        };
    }

    public function getProductTypeLabelAttribute(): string
    {
        return $this->productTypeLabel();
    }
}
