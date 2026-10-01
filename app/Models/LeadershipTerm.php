<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A leadership term such as "2025/2026".
 *
 * Terms preserve history: assignments always belong to a term, so past
 * leadership remains inspectable without overwriting the current structure.
 */
class LeadershipTerm extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * @return HasMany<LeaderAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeaderAssignment::class);
    }

    /**
     * @return HasMany<CommitteeMember, $this>
     */
    public function committeeMembers(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isCurrent(): bool
    {
        return $this->is_active;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
