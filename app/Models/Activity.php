<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'lead_id', 'user_id', 'type', 'subject', 'description', 'activity_at',
    ];

    protected function casts(): array
    {
        return ['activity_at' => 'datetime'];
    }

    const TYPES = ['call', 'email', 'whatsapp', 'meeting', 'note', 'status_change', 'admin_reminder'];

    const TYPE_LABELS = [
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
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
