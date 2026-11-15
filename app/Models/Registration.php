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
        'user_id',
        'product_id',
        'training_schedule_id',
        'training_date',
        'status',
        'amount',
        'notes',
    ];

    protected $appends = ['product_name', 'schedule_info', 'sales_name', 'sales_pic'];

    protected function casts(): array
    {
        return [
            'training_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $registration) {
            if (empty($registration->registration_number)) {
                $registration->registration_number = static::generateNumber();
            }

            // HANYA berstatus confirmed jika customer/lead benar-benar memiliki pembayaran valid yang di-ACC admin
            if ($registration->customer_id) {
                $customer = Customer::find($registration->customer_id);
                if ($customer) {
                    $hasValidPayment = Payment::where('lead_id', $customer->lead_id)->where('status', 'valid')->exists()
                        || Payment::whereHas('lead', fn ($q) => $q->where('phone', $customer->phone))->where('status', 'valid')->exists();

                    if ($hasValidPayment) {
                        if (! $registration->status || $registration->status === 'registered') {
                            $registration->status = 'confirmed';
                        }
                    } else {
                        // Jangan biarkan status confirmed jika pembayaran belum di-ACC admin
                        if (empty($registration->status) || $registration->status === 'confirmed') {
                            $registration->status = 'registered';
                        }
                    }
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSalesNameAttribute(): ?string
    {
        return $this->customer?->lead?->assignedUser?->name
            ?? $this->user?->name
            ?? 'Admin';
    }

    public function getSalesPicAttribute(): ?string
    {
        return $this->getSalesNameAttribute();
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
