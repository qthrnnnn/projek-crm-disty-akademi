<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_code', 'name', 'email', 'phone', 'whatsapp', 'company',
        'job_title', 'city', 'province', 'address',
        'lead_source_id', 'product_id', 'deal_price', 'assigned_to', 'assigned_at',
        'status', 'rejection_reason_id', 'rejection_notes',
        'lead_score', 'priority',
        'first_contacted_at', 'last_contacted_at', 'converted_at', 'lost_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deal_price' => 'decimal:2',
            'assigned_at' => 'datetime',
            'first_contacted_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
            'lost_at' => 'datetime',
            'lead_score' => 'integer',
        ];
    }

    protected $appends = ['product_name'];

    public function getProductNameAttribute(): ?string
    {
        return $this->product?->name ?? 'Program Pelatihan';
    }

    /* ---- Constants ---- */
    const STATUS_NEW = 'new';

    const STATUS_CONTACTED = 'contacted';

    const STATUS_ACTIVE = 'active';

    const STATUS_NEGOTIATION = 'negotiation';

    const STATUS_WON = 'won';

    const STATUS_LOST = 'lost';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_LABELS = [
        'new' => 'Calon Pelanggan (Belum Respon)',
        'contacted' => 'Lead Masuk (Sudah Ada Respon)',
        'active' => 'Lead Aktif',
        'negotiation' => 'Negosiasi Harga',
        'won' => 'Berhasil (Closing)',
        'lost' => 'Lost (Gagal / Bisa Di-Followup Ulang)',
        'inactive' => 'Tidak Aktif',
    ];

    /* ---- Relationships ---- */
    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(SalesReminder::class);
    }

    public function latestPendingReminder()
    {
        return $this->hasOne(SalesReminder::class)->where('is_completed', false)->latestOfMany();
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $raw = $this->whatsapp ?: $this->phone;
        if (! $raw) {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return 'https://wa.me/'.$digits;
    }

    public function getWhatsappFollowUpUrlAttribute(): ?string
    {
        $url = $this->whatsapp_url;
        if (! $url) {
            return null;
        }
        $prod = $this->product?->name ?? 'program pelatihan di Disty Akademi';
        $text = "Halo {$this->name}, salam hangat dari Disty Akademi. Kami ingin menanyakan kelanjutan informasi {$prod}. Apakah ada yang bisa kami bantu jelaskan lebih lanjut?";

        return $url.'?text='.urlencode($text);
    }

    /* ---- Scopes ---- */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeAnswered($query)
    {
        return $query->whereNotNull('first_contacted_at');
    }

    public function scopeUnanswered($query)
    {
        return $query->whereNull('first_contacted_at');
    }

    /* ---- Helpers ---- */
    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function basePrice(): float
    {
        return (float) ($this->product?->price ?? 0);
    }

    public function standardOfferPrice(): float
    {
        // Rumus: Harga Asli + 10% komisi sales
        return round($this->basePrice() * 1.10, 2);
    }

    public function effectivePrice(): float
    {
        if ($this->deal_price !== null && (float) $this->deal_price > 0) {
            return (float) $this->deal_price;
        }

        return $this->standardOfferPrice() ?: $this->basePrice();
    }

    public function estimatedCommission(): float
    {
        $base = $this->basePrice();
        if ($base > 0) {
            return round($base * 0.10, 2);
        }

        return round($this->effectivePrice() * 0.10, 2);
    }

    public function academyRevenue(): float
    {
        // Hak pendapatan bersih Disty Akademi
        return max(0, $this->effectivePrice() - $this->estimatedCommission());
    }

    public function canBeClaimedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Jika belum ditugaskan (pool lead), bisa diambil
        if (is_null($this->assigned_to)) {
            return true;
        }

        // Sesuai arahan: jika sales A lost pelanggan ini, sales B boleh followup customer itu
        if ($this->isLost() && $this->assigned_to !== $user->id) {
            return true;
        }

        return false;
    }

    public static function generateCode(): string
    {
        $prefix = 'LDA-'.now()->format('Ymd');
        $last = static::withTrashed()
            ->where('lead_code', 'like', $prefix.'%')
            ->orderByDesc('lead_code')->first();
        $next = $last ? ((int) substr($last->lead_code, -4)) + 1 : 1;

        return $prefix.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
