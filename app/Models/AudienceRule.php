<?php

namespace App\Models;

use App\Enums\AudienceType;
use Database\Factories\AudienceRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A targeting rule, shared by announcements, notifications, meetings and events.
 *
 * Rules are deliberately shared records (rather than a copy per message) so the
 * targeting vocabulary stays small. `resolved_at` records the last time the rule
 * was materialised for delivery.
 */
class AudienceRule extends Model
{
    /** @use HasFactory<AudienceRuleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'audience_type',
        'audience_value',
        'resolved_at',
    ];

    /**
     * The targeting type.
     *
     * `audience_type` is already cast to the enum, so this returns that value
     * rather than converting it a second time.
     */
    public function type(): ?AudienceType
    {
        return $this->audience_type;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audience_type' => AudienceType::class,
            'resolved_at' => 'datetime',
        ];
    }
}
