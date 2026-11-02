<?php

namespace App\Models;

use App\Enums\PaymentRequestStatus;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentRequest extends Model
{
    use Searchable;
    protected $fillable = [
        'title',
        'description',
        'amount',
        'currency',
        'instructions',
        'due_at',
        'audience',
        'college',
        'programme',
        'year_of_study',
        'hostel',
        'status',
        'created_by',
        'closed_at',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    public function isOpen(): bool
    {
        return $this->status === PaymentRequestStatus::Open
            && ($this->due_at === null || $this->due_at->isFuture());
    }

    public function matchesStudent(StudentProfile $profile): bool
    {
        if (! $profile->isVerified() || $profile->status->value !== 'active') {
            return false;
        }

        return match ($this->audience) {
            'college' => $profile->college === $this->college,
            'programme' => $profile->programme === $this->programme,
            'year' => (int) $profile->year_of_study === (int) $this->year_of_study,
            'hostel' => $profile->hostel !== null && $profile->hostel === $this->hostel,
            default => $this->audience === 'all',
        };
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'year_of_study' => 'integer',
            'status' => PaymentRequestStatus::class,
            'due_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
