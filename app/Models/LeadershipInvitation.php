<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single-use invitation to a leadership role and organisational scope.
 *
 * The plaintext token never touches the database: only its SHA-256 hash is
 * stored. Use LeadershipInvitationService for issue/accept/revoke transitions.
 */
class LeadershipInvitation extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'role_name',
        'ministry_id',
        'committee_id',
        'position_id',
        'leadership_term_id',
        'invited_by',
        'token_hash',
        'status',
        'expires_at',
        'accepted_at',
        'declined_at',
        'revoked_at',
        'accepted_by',
        'notes',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
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
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
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
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::Pending->value);
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }

    public function role(): ?RoleName
    {
        return RoleName::tryFrom($this->role_name);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
