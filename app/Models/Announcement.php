<?php

namespace App\Models;

use App\Enums\AnnouncementStatus;
use App\Enums\Priority;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Information intended for an audience.
 *
 * Lifecycle: draft → review → published → expired/archived. Readers only ever
 * see published items whose audience resolves to them.
 */
class Announcement extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'author_id',
        'priority',
        'status',
        'published_at',
        'expires_at',
        'requires_approval',
        'approved_by',
        'attachment_path',
        'attachment_name',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return BelongsToMany<AudienceRule, $this>
     */
    public function audienceRules(): BelongsToMany
    {
        return $this->belongsToMany(AudienceRule::class, 'announcement_audience_rules');
    }

    /**
     * Attachments are stored files; the relation exists so announcements,
     * complaints, meetings and events share one attachment pipeline.
     *
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isPublished(): bool
    {
        return $this->status === AnnouncementStatus::Published;
    }

    /**
     * Whether the announcement should be hidden because it has passed its
     * expiry date. Expiry is computed on read so no scheduled job is required.
     */
    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Published and still within its validity window.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeVisibleToAudience(Builder $query): Builder
    {
        return $query->where('status', AnnouncementStatus::Published->value)
            ->where(fn (Builder $inner) => $inner->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', AnnouncementStatus::Published->value);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForAuthor(Builder $query, User $author): Builder
    {
        return $query->where('author_id', $author->getKey());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'status' => AnnouncementStatus::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'requires_approval' => 'boolean',
        ];
    }
}
