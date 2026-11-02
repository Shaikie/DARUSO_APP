<?php

namespace App\Models;

use App\Enums\CalendarEntryType;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A custom entry on the shared calendar.
 */
class CalendarEntry extends Model
{
    use Searchable;
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'start_date',
        'end_date',
        'start_time',
        'location',
        'is_all_day',
        'audience',
        'created_by',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->where(function (Builder $inner) use ($from, $to): void {
            $inner->whereBetween('start_date', [$from, $to])
                ->orWhereBetween('end_date', [$from, $to])
                ->orWhere(function (Builder $span) use ($from, $to): void {
                    $span->where('start_date', '<=', $from)->where('end_date', '>=', $to);
                });
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CalendarEntryType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'is_all_day' => 'boolean',
        ];
    }
}
