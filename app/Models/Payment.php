<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'lead_id', 'product_id', 'verified_by', 'amount',
        'payment_method', 'bank_name', 'account_number', 'account_name',
        'proof_file', 'payment_date', 'status',
        'verification_notes', 'verified_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }
}
