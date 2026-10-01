<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A DARUSO ministry. Ministries drive complaint routing and targeted
 * communication, so they are data rather than code.
 */
class Ministry extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return HasMany<LeaderAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeaderAssignment::class);
    }

    /**
     * @return HasMany<Complaint, $this>
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_ministry_id');
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'ministry_id');
    }

    /**
     * Leaders currently holding this ministry in the active term.
     *
     * @return HasMany<LeaderAssignment, $this>
     */
    public function activeAssignments(): HasMany
    {
        return $this->assignments()->whereHas('term', fn ($query) => $query->where('is_active', true));
    }
}
