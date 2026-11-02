<?php

namespace App\Models;

use App\Enums\FeedbackStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student suggestion or feedback item.
 */
class Feedback extends Model
{
    use Searchable;
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'category',
        'status',
        'is_anonymous',
        'response',
        'responded_by',
        'responded_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [FeedbackStatus::Open->value, FeedbackStatus::InReview->value]);
    }

    public function isAnonymous(): bool
    {
        return (bool) $this->is_anonymous;
    }

    /**
     * Display name that respects anonymity for readers other than the author.
     */
    public function displayAuthor(?User $viewer = null): string
    {
        if ($this->isAnonymous() && $viewer?->getKey() !== $this->user_id) {
            return 'Anonymous student';
        }

        return $this->author?->name ?? 'Former student';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FeedbackStatus::class,
            'is_anonymous' => 'boolean',
            'responded_at' => 'datetime',
        ];
    }
}
