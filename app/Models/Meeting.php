<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A scheduled leadership meeting with a targeted audience.
 */
class Meeting extends Model
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
        'meeting_date',
        'meeting_time',
        'venue',
        'status',
        'agenda',
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
        return $this->belongsToMany(AudienceRule::class, 'meeting_audience_rules');
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
        return $query->where('meeting_date', '>=', today())
            ->where('status', MeetingStatus::Scheduled->value)
            ->orderBy('meeting_date')
            ->orderBy('meeting_time');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
            'meeting_time' => 'datetime:H:i',
            'status' => MeetingStatus::class,
        ];
    }
}
