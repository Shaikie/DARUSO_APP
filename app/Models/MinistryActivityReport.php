<?php

namespace App\Models;

use App\Enums\ActivityReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A periodic report on a ministry's activity.
 */
class MinistryActivityReport extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'ministry_id',
        'leadership_term_id',
        'submitted_by',
        'period_start',
        'period_end',
        'summary',
        'achievements',
        'challenges',
        'recommendations',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    /**
     * @return BelongsTo<Ministry, $this>
     */
    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * @return BelongsTo<LeadershipTerm, $this>
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(LeadershipTerm::class, 'leadership_term_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForMinistries(Builder $query, iterable $ministryIds): Builder
    {
        return $query->whereIn('ministry_id', collect($ministryIds)->all());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ActivityReportStatus::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }
}
