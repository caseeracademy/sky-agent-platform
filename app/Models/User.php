<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_agent_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent agent (for agent staff users).
     */
    public function parentAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_agent_id');
    }

    /**
     * Get the staff members (for agent owner users).
     */
    public function agentStaff(): HasMany
    {
        return $this->hasMany(User::class, 'parent_agent_id');
    }

    /**
     * Get the students managed by this agent.
     */
    /**
     * Get the students belonging to this agent.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'agent_id');
    }

    /**
     * Get the applications created by this agent.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'agent_id');
    }

    /**
     * Get the applications assigned to this admin.
     */
    public function assignedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'assigned_admin_id');
    }

    /**
     * Get the commissions earned by this agent.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'agent_id');
    }

    /**
     * Get the payouts requested by this agent.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'agent_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'agent_id');
    }

    /**
     * Scope to get only super admin users.
     */
    public function scopeSuperAdmin(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('role', 'super_admin');
    }

    /**
     * Scope to get only admin staff users.
     */
    public function scopeAdminStaff(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('role', 'admin_staff');
    }

    /**
     * Scope to get only agent owner users.
     */
    public function scopeAgentOwner(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('role', 'agent_owner');
    }

    /**
     * Scope to get only agent staff users.
     */
    public function scopeAgentStaff(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('role', 'agent_staff');
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Check if user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an Admin Staff.
     */
    public function isAdminStaff(): bool
    {
        return $this->role === 'admin_staff';
    }

    /**
     * Check if user is an Agent Owner.
     */
    public function isAgentOwner(): bool
    {
        return $this->role === 'agent_owner';
    }

    /**
     * Check if user is an Agent Staff.
     */
    public function isAgentStaff(): bool
    {
        return $this->role === 'agent_staff';
    }
}
