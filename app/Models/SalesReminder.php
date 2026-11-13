<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'commission_id',
        'admin_id',
        'sales_id',
        'type',
        'message',
        'priority',
        'is_read',
        'read_at',
        'is_completed',
        'completed_at',
        'sales_reply',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_completed' => 'boolean',
            'read_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }

    public function isCommissionPaid(): bool
    {
        return $this->type === 'commission_paid';
    }

    public function isFollowUp(): bool
    {
        return $this->type === 'follow_up';
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAsCompleted(?string $reply = null): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => $this->read_at ?: now(),
            'is_completed' => true,
            'completed_at' => now(),
            'sales_reply' => $reply ?: $this->sales_reply,
        ]);
    }
}
