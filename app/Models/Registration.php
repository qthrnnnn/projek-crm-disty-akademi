<?php

namespace App\Models;

use App\Support\SequentialCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

            if ($registration->status !== 'cancelled') {
                $registration->status = 'registered';
            }
        });

        static::created(function (Registration $registration) {
            if ($registration->status !== 'registered' || ! $registration->customer?->lead_id) {
                return;
            }

            $hasPreviousRegistration = static::where('customer_id', $registration->customer_id)
                ->where('product_id', $registration->product_id)
                ->whereKeyNot($registration->id)
                ->where('status', '!=', 'cancelled')
                ->exists();
            if ($hasPreviousRegistration) {
                return;
            }

            // Pembayaran sebelum pendaftaran pertama hanya dapat digunakan untuk satu pendaftaran.
            $prepayment = Payment::where('lead_id', $registration->customer->lead_id)
                ->where('product_id', $registration->product_id)
                ->where('status', 'valid')
                ->whereNull('registration_id')
                ->latest('id')
                ->first();
            if ($prepayment && Payment::whereKey($prepayment->id)->whereNull('registration_id')->updateWithHistory(['registration_id' => $registration->id])) {
                $registration->update(['status' => 'confirmed']);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeAwaitingPaymentSubmission(Builder $query): Builder
    {
        return $query->where('registrations.status', 'registered')
            ->whereHas('customer')
            ->whereDoesntHave('payments', fn (Builder $payment) => $payment->submitted())
            ->whereDoesntHave('customer.lead.payments', fn (Builder $payment) => $payment->submitted()->matchingLegacyRegistration());
    }

    public function scopeHandledBy(Builder $query, int $salesId): Builder
    {
        return $query->where(fn (Builder $registration) => $registration
            ->whereHas('customer', fn (Builder $customer) => $customer->withLeadAssignedTo($salesId))
            ->orWhere(fn (Builder $fallback) => $fallback
                ->where('user_id', $salesId)
                ->whereHas('customer', fn (Builder $customer) => $customer->withoutAssignedLead())));
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
        return SequentialCode::next($prefix, $last?->registration_number);
    }
}
