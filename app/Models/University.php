<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the programs for the university.
     */
    public function programs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Scope to get only active universities.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_active', true);
    }
}
