<?php

namespace App\Models;

use App\Enums\PaymentSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmission extends Model
{
    protected $fillable = [
        'payment_request_id',
        'user_id',
        'amount_paid',
        'transaction_reference',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'status' => PaymentSubmissionStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
