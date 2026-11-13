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
    ];

    protected function casts(): array
    {
        return [
            'follow_up_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
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
