<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agent_id',
        'name', // Keep for backward compatibility
        'first_name',
        'middle_name',
        'last_name',
        'passport_number',
        'mothers_name',
        'nationality',
        'email',
        'phone',
        'phone_number',
        'country_of_residence',
        'date_of_birth',
        'profile_image',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the agent that owns the student.
     */
    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Scope to get students for a specific agent.
     */
    public function scopeForAgent(\Illuminate\Database\Eloquent\Builder $query, int $agentId): void
    {
        $query->where('agent_id', $agentId);
    }

    /**
     * Get the student's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Get the student's full name and email for display.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->getFullNameAttribute();

        return "{$name} ({$this->email})";
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            $name = $this->first_name;
            if ($this->middle_name) {
                $name .= ' '.$this->middle_name;
            }
            $name .= ' '.$this->last_name;

            return $name;
        }

        // Fallback to old 'name' field for backward compatibility
        return $this->name ?? 'Unknown Student';
    }

    /**
     * Get the student's full name for display in forms.
     */
    public function getNameAttribute(): ?string
    {
        // If we have the new fields, use them
        if ($this->first_name && $this->last_name) {
            return $this->getFullNameAttribute();
        }

        // Otherwise return the stored name (for backward compatibility)
        return $this->attributes['name'] ?? null;
    }

    /**
     * Get the applications for the student.
     */
    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get the documents for the student.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_image);
    }

    /**
     * Get documents by type.
     */
    public function getDocumentsByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return $this->documents()->where('type', $type)->get();
    }

    /**
     * Check if student has documents of a specific type.
     */
    public function hasDocumentType(string $type): bool
    {
        return $this->documents()->where('type', $type)->exists();
    }
}
