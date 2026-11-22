<?php

namespace App\Models;

use App\Support\SequentialCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'nik',
        'birth_date',
        'document_number',
        'institution_id',
        'lead_id',
        'city',
        'province',
        'status',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    protected $appends = ['company', 'address', 'nik', 'birth_date'];

    public function getNikAttribute(): ?string
    {
        return $this->attributes['nik'] ?? ($this->attributes['document_number'] ?? $this->lead?->nik);
    }

    public function getBirthDateAttribute(): ?string
    {
        return $this->attributes['birth_date'] ?? $this->lead?->birth_date;
    }

    public function getCompanyAttribute(): ?string
    {
        return $this->institution?->name ?? $this->lead?->company;
    }

    public function getAddressAttribute(): ?string
    {
        return $this->attributes['address'] ?? ($this->institution?->address ?? ($this->city ? ($this->province ? "{$this->city}, {$this->province}" : $this->city) : null));
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function leads(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class)->orderByDesc('id');
    }

    public function scopeWithLeadAssignedTo(Builder $query, int $salesId): Builder
    {
        return $query->whereHas('lead', fn (Builder $lead) => $lead->where('assigned_to', $salesId));
    }

    public function scopeWithoutAssignedLead(Builder $query): Builder
    {
        return $query->whereDoesntHave('lead', fn (Builder $lead) => $lead->whereNotNull('assigned_to'));
    }

    public function scopeHandledBy(Builder $query, int $salesId): Builder
    {
        return $query->where(fn (Builder $customer) => $customer
            ->withLeadAssignedTo($salesId)
            ->orWhere(fn (Builder $fallback) => $fallback
                ->withoutAssignedLead()
                ->whereHas('registrations', fn (Builder $registration) => $registration->where('user_id', $salesId))));
    }

    public static function generateCode(): string
    {
        $prefix = 'CUST-'.now()->format('Ym');
        $last = static::withTrashed()
            ->where('customer_code', 'like', $prefix.'%')
            ->orderByDesc('customer_code')->first();
        return SequentialCode::next($prefix, $last?->customer_code);
    }
}
