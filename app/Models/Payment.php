<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'lead_id', 'product_id', 'registration_id', 'verified_by', 'amount',
        'payment_method', 'bank_name', 'account_number', 'account_name',
        'proof_file', 'payment_date', 'status',
        'verification_notes', 'verified_at', 'notes',
    ];

    protected $appends = ['proof_url'];

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_file ? asset('storage/'.$this->proof_file) : null;
    }

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

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->whereIn('payments.status', ['pending', 'valid']);
    }

    public function scopeMatchingLegacyRegistration(Builder $query): Builder
    {
        // Pendaftaran pembanding tersedia di kueri utama atau melalui join.
        return $query->whereNull('payments.registration_id')
            ->whereColumn('payments.product_id', 'registrations.product_id')
            ->whereColumn('payments.created_at', '>=', 'registrations.created_at')
            ->whereNotExists(function ($later) {
                $later->selectRaw('1')->from('registrations as later_registrations')
                    ->whereColumn('later_registrations.customer_id', 'registrations.customer_id')
                    ->whereColumn('later_registrations.product_id', 'registrations.product_id')
                    ->whereColumn('later_registrations.id', '>', 'registrations.id')
                    ->whereColumn('later_registrations.created_at', '<=', 'payments.created_at')
                    ->where('later_registrations.status', '!=', 'cancelled');
            });
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    public function processVerification(string $status, int $verifiedBy, ?string $notes = null): void
    {
        $registration = $this->registration;
        if (! $registration && $status === 'valid' && $this->product_id) {
            $registration = Registration::where('product_id', $this->product_id)
                ->whereHas('customer', fn ($query) => $query->where('lead_id', $this->lead_id))
                ->where('status', '!=', 'cancelled')
                ->where('created_at', '<=', $this->created_at)
                ->latest('id')
                ->first();
        }

        $this->update([
            'registration_id' => $registration?->id,
            'status' => $status,
            'verification_notes' => $notes ?? $this->verification_notes,
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);

        if ($status === 'valid') {
            $lead = $this->lead;
            if ($lead) {
                // 1. Update lead ke won
                $lead->update([
                    'status' => 'won',
                    'converted_at' => $lead->converted_at ?: now(),
                ]);

                // 2. Pastikan Customer dibuat / diaktifkan
                $customer = $registration?->customer ?? $lead->customer;
                if (!$customer) {
                    $customer = app(\App\Http\Controllers\LeadController::class)->ensureCustomerCreated($lead);
                }

                if ($customer) {
                    $customer->update([
                        'status' => 'active',
                        'lead_id' => $lead->id,
                    ]);
                }

                // 3. Konfirmasi pendaftaran yang dibayar.
                if ($registration?->status === 'registered') {
                    $registration->update(['status' => 'confirmed']);
                }

                // 4. Pastikan Komisi 10% tercatat dan siap untuk sales penanggung jawab
                Commission::ensureCommissionCreated(
                    $lead,
                    (float) ($this->amount ?: $lead->deal_price),
                    $this->id
                );

                // 5. Otomatis selesaikan follow-up & pengingat
                $lead->followUps()->where('status', 'pending')->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'result_notes' => 'Otomatis selesai: Pembayaran valid & deal won',
                ]);
                SalesReminder::where('lead_id', $lead->id)
                    ->where('is_completed', false)
                    ->update([
                        'is_completed' => true,
                        'completed_at' => now(),
                        'sales_reply' => 'Selesai: Pembayaran terverifikasi valid',
                    ]);

                // 6. Log aktivitas
                Activity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $verifiedBy,
                    'type' => 'status_change',
                    'subject' => 'Pembayaran Valid - Status diubah ke Berhasil (Won)',
                    'description' => 'Pembayaran Rp '.number_format((float) $this->amount, 0, ',', '.').' diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.',
                    'activity_at' => now(),
                ]);
            }
        } elseif ($status === 'invalid') {
            $lead = $this->lead;
            if ($lead) {
                $salesId = $lead->assigned_to;
                $reason = $notes ?: ($this->verification_notes ?: 'Bukti transfer tidak sesuai atau tidak valid.');

                // 1. Kirim notifikasi ke akun Sales yang menangani
                if ($salesId) {
                    SalesReminder::create([
                        'lead_id' => $lead->id,
                        'admin_id' => $verifiedBy,
                        'sales_id' => $salesId,
                        'type' => 'payment_rejected',
                        'message' => "Bukti pembayaran peserta {$lead->name} dinyatakan TIDAK VALID oleh Admin. Alasan: \"{$reason}\". Silakan hubungi peserta untuk konfirmasi ulang bukti transfer yang sah.",
                        'priority' => 'urgent',
                        'is_read' => false,
                        'is_completed' => false,
                    ]);
                }

                // 2. Batalkan komisi jika sebelumnya pernah dibuat
                Commission::where('payment_id', $this->id)->delete();

                if ($registration?->status === 'confirmed' && ! self::where('registration_id', $registration->id)->where('status', 'valid')->exists()) {
                    $registration->update(['status' => 'registered']);
                }

                // 3. Kembalikan status prospek dan customer jika belum ada pembayaran valid lain.
                $hasOtherValidPayment = Payment::where('lead_id', $lead->id)
                    ->where('id', '!=', $this->id)
                    ->where('status', 'valid')
                    ->exists();

                if (! $hasOtherValidPayment) {
                    $lead->update(['status' => 'negotiation']);
                    if ($lead->customer) {
                        $lead->customer->update(['status' => 'prospect']);
                    }
                }

                // 4. Log aktivitas
                Activity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $verifiedBy,
                    'type' => 'status_change',
                    'subject' => 'Pembayaran Ditolak (Tidak Valid)',
                    'description' => 'Pembayaran Rp '.number_format((float) $this->amount, 0, ',', '.').' dinyatakan TIDAK VALID oleh admin. Alasan: "'.$reason.'". Notifikasi telah dikirimkan ke akun sales penanggung jawab.',
                    'activity_at' => now(),
                ]);
            }
        } elseif ($status === 'recheck') {
            $lead = $this->lead;
            if ($lead) {
                $salesId = $lead->assigned_to;
                $reason = $notes ?: ($this->verification_notes ?: 'Perlu konfirmasi ulang bukti transfer.');

                if ($salesId) {
                    SalesReminder::create([
                        'lead_id' => $lead->id,
                        'admin_id' => $verifiedBy,
                        'sales_id' => $salesId,
                        'type' => 'payment_rejected',
                        'message' => "Bukti pembayaran peserta {$lead->name} PERLU DICEK ULANG. Catatan Admin: \"{$reason}\". Silakan konfirmasi ulang kepada peserta.",
                        'priority' => 'urgent',
                        'is_read' => false,
                        'is_completed' => false,
                    ]);
                }

                Activity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $verifiedBy,
                    'type' => 'note',
                    'subject' => 'Pembayaran Perlu Cek Ulang',
                    'description' => 'Bukti pembayaran Rp '.number_format((float) $this->amount, 0, ',', '.').' memerlukan konfirmasi ulang. Catatan: "'.$reason.'".',
                    'activity_at' => now(),
                ]);
            }
        }
    }
}
