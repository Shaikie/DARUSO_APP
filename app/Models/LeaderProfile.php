<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Marks a user as a leader. Concrete roles live in leader_assignments and
 * committee_members so the structure stays database-driven.
 */
class LeaderProfile extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bio',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<LeaderAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeaderAssignment::class, 'user_id', 'user_id');
    }
}
