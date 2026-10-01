<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A student complaint.
 *
 * Complaints are private to their creator plus the leaders explicitly permitted
 * to act on them; that rule lives in ComplaintPolicy rather than in the UI.
 */
class Complaint extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'creator_id',
        'assigned_ministry_id',
        'assigned_leader_id',
        'resolved_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * @return BelongsTo<Ministry, $this>
     */
    public function assignedMinistry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class, 'assigned_ministry_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_leader_id');
    }

    /**
     * @return HasMany<ComplaintHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(ComplaintHistory::class)->latest();
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
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            ComplaintStatus::Resolved->value,
            ComplaintStatus::Closed->value,
            ComplaintStatus::Rejected->value,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ComplaintStatus::class,
            'resolved_at' => 'datetime',
        ];
    }
}
