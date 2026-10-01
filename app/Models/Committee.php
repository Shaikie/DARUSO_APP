<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A DARUSO committee, e.g. Finance or Disciplinary.
 */
class Committee extends Model
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
     * @return HasMany<CommitteeMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'committee_id');
    }

    /**
     * @return HasMany<CommitteeMember, $this>
     */
    public function activeMembers(): HasMany
    {
        return $this->members()->whereHas('term', fn ($query) => $query->where('is_active', true));
    }
}
