<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable journal entry recording a complaint state change.
 */
class ComplaintHistory extends Model
{
    use HasFactory;

    /**
     * The table has a single timestamp column, matching the documented design.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'complaint_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'complaint_id',
        'actor_id',
        'action',
        'old_status',
        'new_status',
        'notes',
    ];

    /**
     * @return BelongsTo<Complaint, $this>
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_status' => ComplaintStatus::class,
            'new_status' => ComplaintStatus::class,
            'created_at' => 'datetime',
        ];
    }
}
