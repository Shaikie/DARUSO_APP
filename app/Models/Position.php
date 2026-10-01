<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A configurable leadership position, ordered by hierarchy_level.
 */
class Position extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'hierarchy_level',
    ];

    /**
     * @return HasMany<LeaderAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeaderAssignment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hierarchy_level' => 'integer',
        ];
    }
}
