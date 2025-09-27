<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'university_id',
        'name',
        'tuition_fee',
        'agent_commission',
        'system_commission',
        'degree_type',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tuition_fee' => 'decimal:2',
            'agent_commission' => 'decimal:2',
            'system_commission' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the university that owns the program.
     */
    public function university(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Scope to get only active programs.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to get programs by degree type.
     */
    public function scopeByDegreeType(\Illuminate\Database\Eloquent\Builder $query, string $degreeType): void
    {
        $query->where('degree_type', $degreeType);
    }

    /**
     * Get the applications for the program.
     */
    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Application::class);
    }
}
