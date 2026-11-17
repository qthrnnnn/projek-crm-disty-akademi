<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = [
        'user_id', 'lead_id', 'payment_id',
        'deal_amount', 'commission_rate', 'commission_amount',
        'bank_name', 'bank_account_number', 'bank_account_name',
        'status', 'approved_at', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'deal_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected $appends = ['amount', 'bank_info'];

    public function getAmountAttribute(): float
    {
        return (float) ($this->attributes['commission_amount'] ?? 0);
    }

    public function getBankInfoAttribute(): string
    {
        // 1. Prioritas utama: snapshot rekening yang tersimpan permanen pada transaksi komisi ini
        if ($this->bank_name && $this->bank_account_number) {
            return "{$this->bank_name} - {$this->bank_account_number}" . ($this->bank_account_name ? " (a.n {$this->bank_account_name})" : "");
        }

        // 2. Fallback: rekening aktif sales saat ini
        if ($this->user && $this->user->bank_name && $this->user->bank_account_number) {
            return "{$this->user->bank_name} - {$this->user->bank_account_number}" . ($this->user->bank_account_name ? " (a.n {$this->user->bank_account_name})" : "");
        }

        return 'Belum diatur';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Otomatis membuat atau menyinkronkan data komisi 10% untuk Sales saat Lead Won/Closing.
     * Rumus: Customer mentransfer harga katalog + 10% komisi sales.
     */
    public static function ensureCommissionCreated(Lead $lead, ?float $amount = null, ?int $paymentId = null): ?Commission
    {
        $salesUserId = $lead->assigned_to ?: (auth()->id() ?: 1);
        $payment = $paymentId ? Payment::find($paymentId) : null;
        $product = $payment?->product ?: $lead->product;
        $baseKatalog = (float) ($product?->price ?: 0);

        if ($baseKatalog > 0) {
            $commissionAmount = round($baseKatalog * 0.10, 2);
            $dealAmount = $amount ?: ($lead->deal_price ?: round($baseKatalog * 1.10, 2));
        } else {
            $dealAmount = (float) ($amount ?: ($lead->deal_price ?: 1831500));
            $baseKatalog = round($dealAmount / 1.10, 2);
            $commissionAmount = round($baseKatalog * 0.10, 2);
        }

        $rate = 10.00;

        $commission = null;
        if ($paymentId) {
            $commission = self::where('payment_id', $paymentId)->first();
        }
        if (! $commission) {
            $commission = self::where('lead_id', $lead->id)->whereNull('payment_id')->first();
        }

        $salesUser = User::find($salesUserId);
        $bankName = $salesUser?->bank_name;
        $bankAccountNumber = $salesUser?->bank_account_number;
        $bankAccountName = $salesUser?->bank_account_name ?: $salesUser?->name;

        if (! $commission) {
            $commission = self::create([
                'user_id' => $salesUserId,
                'lead_id' => $lead->id,
                'payment_id' => $paymentId,
                'deal_amount' => $dealAmount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'bank_account_name' => $bankAccountName,
                'status' => 'pending',
            ]);
        } else {
            if ($paymentId && ! $commission->payment_id) {
                $commission->payment_id = $paymentId;
            }
            if ($commission->status === 'pending') {
                if ($dealAmount > 0 && ($commission->deal_amount <= 0 || $amount)) {
                    $commission->deal_amount = $dealAmount;
                    $commission->commission_amount = $commissionAmount;
                    $commission->user_id = $salesUserId;
                }
                if (! $commission->bank_account_number) {
                    $commission->bank_name = $bankName;
                    $commission->bank_account_number = $bankAccountNumber;
                    $commission->bank_account_name = $bankAccountName;
                }
            }
            $commission->save();
        }

        return $commission;
    }
}
