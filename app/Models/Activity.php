<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Activity extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Activity $activity) {
            if (! Schema::hasColumn('activities', 'actor_name')) {
                return;
            }
            // Keep names for manual notes and legacy workflow entries as well.
            $lead = $activity->lead_id ? Lead::withTrashed()->find($activity->lead_id) : null;
            $activity->lead_name ??= $lead?->name;
            $activity->owner_id ??= $lead?->assigned_to;
            $activity->actor_name ??= $activity->user_id
                ? User::withTrashed()->whereKey($activity->user_id)->value('name')
                : 'Sistem';
        });
    }

    protected $fillable = [
        'lead_id', 'user_id', 'type', 'subject', 'description', 'activity_at',
        'entity_type', 'entity_id', 'entity_name', 'actor_name', 'lead_name', 'owner_id', 'changes',
    ];

    protected function casts(): array
    {
        return ['activity_at' => 'datetime', 'changes' => 'array'];
    }

    const TYPES = ['call', 'email', 'whatsapp', 'meeting', 'note', 'status_change', 'admin_reminder', 'created', 'updated', 'deleted', 'restored'];

    const TYPE_LABELS = [
        'created' => 'Tambah Data',
        'updated' => 'Ubah Data',
        'deleted' => 'Hapus Data',
        'restored' => 'Pulihkan Data',
        'call' => 'Telepon',
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
        'meeting' => 'Meeting',
        'note' => 'Catatan',
        'status_change' => 'Ubah Status',
        'admin_reminder' => 'Instruksi Admin',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user?->isSales()) {
            $query->where(fn (Builder $visible) => $visible
                ->where('user_id', $user->id)
                ->orWhere('owner_id', $user->id)
                ->orWhereHas('lead', fn (Builder $lead) => $lead->where('assigned_to', $user->id)));
        }

        return $query;
    }
}
