<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A university-wide or audience-targeted DARUSO event.
 */
class Event extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'organizer_id',
        'event_date',
        'venue',
        'status',
        'attachment_path',
        'attachment_name',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * @return BelongsToMany<AudienceRule, $this>
     */
    public function audienceRules(): BelongsToMany
    {
        return $this->belongsToMany(AudienceRule::class, 'event_audience_rules');
    }

    /**
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_date', '>=', today())
            ->where('status', EventStatus::Upcoming->value)
            ->orderBy('event_date');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'status' => EventStatus::class,
        ];
    }
}
