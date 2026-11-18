<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $fillable = [
        'lead_id', 'assigned_to', 'title', 'description',
        'follow_up_at', 'method', 'priority', 'status',
        'result_notes', 'completed_at',
        'type', 'scheduled_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FollowUp $followUp) {
            if (empty($followUp->method) && ! empty($followUp->attributes['type'])) {
                $followUp->method = $followUp->attributes['type'];
            }
            if (empty($followUp->follow_up_at) && ! empty($followUp->attributes['scheduled_at'])) {
                $followUp->follow_up_at = $followUp->attributes['scheduled_at'];
            }
            if (empty($followUp->description) && ! empty($followUp->attributes['notes'])) {
                $followUp->description = $followUp->attributes['notes'];
            }

            if (empty($followUp->title)) {
                $lead = $followUp->lead;
                $method = ucfirst($followUp->method ?: 'WhatsApp');
                $name = $lead ? $lead->name : 'Peserta';
                $followUp->title = "Follow-Up ({$method}) - {$name}";
            }
            if (empty($followUp->method)) {
                $followUp->method = 'whatsapp';
            }
            if (empty($followUp->priority)) {
                $followUp->priority = 'normal';
            }
            if (empty($followUp->status)) {
                $followUp->status = 'pending';
            }
            if (empty($followUp->follow_up_at)) {
                $followUp->follow_up_at = now()->addDays(1);
            }
            if (empty($followUp->assigned_to)) {
                $followUp->assigned_to = auth()->id() ?? ($followUp->lead?->assigned_to ?? 1);
            }

            // Hapus field alias agar tidak memicu error unknown column di SQL
            unset($followUp->type, $followUp->scheduled_at, $followUp->notes);
        });
    }

    public function setTypeAttribute($val): void
    {
        $this->attributes['method'] = $val;
    }

    public function setScheduledAtAttribute($val): void
    {
        $this->attributes['follow_up_at'] = $val;
    }

    public function setNotesAttribute($val): void
    {
        $this->attributes['description'] = $val;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')->where('follow_up_at', '<', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('follow_up_at', today());
    }
}
