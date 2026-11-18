<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'is_active',
        'bank_name', 'bank_account_number', 'bank_account_name',
    ];

    protected $appends = ['bank_info'];

    public function getBankInfoAttribute(): string
    {
        if ($this->bank_name && $this->bank_account_number) {
            return "{$this->bank_name} - {$this->bank_account_number}".($this->bank_account_name ? " (a.n {$this->bank_account_name})" : '');
        }

        return 'Belum diatur';
    }

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
