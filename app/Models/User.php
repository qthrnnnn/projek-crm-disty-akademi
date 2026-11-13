<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assigned_to');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function sentReminders(): HasMany
    {
        return $this->hasMany(SalesReminder::class, 'admin_id');
    }

    public function receivedReminders(): HasMany
    {
        return $this->hasMany(SalesReminder::class, 'sales_id');
    }

    public function unreadRemindersCount(): int
    {
        return $this->receivedReminders()->where('is_read', false)->count();
    }

    public function activeReminders()
    {
        return $this->receivedReminders()
            ->with(['lead.product', 'admin'])
            ->where('is_completed', false)
            ->latest();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }
}
