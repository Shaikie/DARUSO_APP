<?php

namespace App\Models;

use App\Enums\HandoverStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Institutional knowledge handed from one officer to their successor.
 */
class Handover extends Model
{
    use Searchable;
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'summary',
        'outstanding_items',
        'recommendations',
        'from_user_id',
        'to_user_id',
        'ministry_id',
        'committee_id',
        'leadership_term_id',
        'status',
        'submitted_at',
        'acknowledged_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * @return BelongsTo<Ministry, $this>
     */
    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * @return BelongsTo<Committee, $this>
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * @return BelongsTo<LeadershipTerm, $this>
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(LeadershipTerm::class, 'leadership_term_id');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('from_user_id', $user->getKey())
            ->orWhere('to_user_id', $user->getKey());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => HandoverStatus::class,
            'submitted_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }
}
