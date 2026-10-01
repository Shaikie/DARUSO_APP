<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only record of a sensitive action.
 *
 * The model refuses updates and deletes so the trail cannot be rewritten from
 * application code; there is likewise no route that mutates a log.
 */
class AuditLog extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * Audit rows are written by the application, not mass-assigned from requests.
     *
     * @var list<string>
     */
    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_id',
        'action',
        'target_type',
        'target_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function isImmutable(): bool
    {
        return true;
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForTarget(Builder $query, Model $target): Builder
    {
        return $query->where('target_type', $target->getMorphClass())
            ->where('target_id', $target->getKey());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new \LogicException('Audit log entries are immutable.');
        });

        static::deleting(function (): never {
            throw new \LogicException('Audit log entries cannot be deleted.');
        });
    }
}
