<?php

namespace App\Models;

use App\Enums\StudentStatus;
use App\Models\Concerns\Searchable;
use Database\Factories\StudentProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Academic record attached to a user.
 *
 * @property int $year_of_study
 */
class StudentProfile extends Model
{
    /** @use HasFactory<StudentProfileFactory> */
    use HasFactory;

    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'registration_number',
        'college',
        'school_faculty',
        'programme',
        'year_of_study',
        'hostel',
        'gender',
        'status',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Active->value);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOfCollege(Builder $query, string $college): Builder
    {
        return $query->where('college', $college);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOfProgramme(Builder $query, string $programme): Builder
    {
        return $query->where('programme', $programme);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOfYear(Builder $query, int $year): Builder
    {
        return $query->where('year_of_study', $year);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOfHostel(Builder $query, string $hostel): Builder
    {
        return $query->where('hostel', $hostel);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year_of_study' => 'integer',
            'status' => StudentStatus::class,
        ];
    }
}
