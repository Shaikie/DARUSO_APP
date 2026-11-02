<?php

namespace App\Models;

use App\Enums\PollStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A community poll.
 */
class Poll extends Model
{
    use Searchable;
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'status',
        'is_anonymous',
        'allow_multiple',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<PollOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<PollVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [PollStatus::Open->value, PollStatus::Closed->value]);
    }

    public function isOpen(): bool
    {
        if ($this->status !== PollStatus::Open) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        return $this->ends_at === null || $this->ends_at->isFuture();
    }

    public function hasEnded(): bool
    {
        return $this->status === PollStatus::Closed
            || ($this->ends_at !== null && $this->ends_at->isPast());
    }

    public function totalVotes(): int
    {
        return (int) ($this->votes_count ?? $this->votes()->count());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PollStatus::class,
            'is_anonymous' => 'boolean',
            'allow_multiple' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
