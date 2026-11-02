<?php

namespace App\Services;

use App\Enums\PaymentRequestStatus;
use App\Enums\PaymentSubmissionStatus;
use App\Enums\Priority;
use App\Enums\StudentStatus;
use App\Enums\VerificationStatus;
use App\Models\PaymentRequest;
use App\Models\PaymentSubmission;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentRequestService
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function create(array $data, User $creator): PaymentRequest
    {
        $request = DB::transaction(fn (): PaymentRequest => PaymentRequest::create([
            ...$data,
            'currency' => 'TZS',
            'status' => PaymentRequestStatus::Open,
            'created_by' => $creator->getKey(),
        ]));

        $targets = $this->targetUsers($request);
        if ($targets->isEmpty()) {
            $request->delete();

            throw new \DomainException('No verified active students match the selected audience.');
        }

        foreach ($targets as $student) {
            $this->notifications->sendToUser(
                recipient: $student,
                sender: $creator,
                title: 'Payment reminder: '.$request->title,
                message: 'A payment request of TZS '.number_format((float) $request->amount, 2).' is available. Due: '.($request->due_at?->format('d M Y H:i') ?? 'No due date set').'.',
                priority: Priority::Normal,
                related: $request,
                actionUrl: route('student.payments.show', $request),
            );
        }

        $this->audit->log('payment_request_created', $request, null, [
            'amount' => $request->amount,
            'audience' => $request->audience,
            'recipient_count' => $targets->count(),
        ]);

        return $request;
    }

    public function visibleTo(User $student): Collection
    {
        $profile = $student->studentProfile;

        if ($profile === null || ! $student->canParticipate()) {
            return collect();
        }

        return PaymentRequest::query()
            ->whereIn('status', [PaymentRequestStatus::Open->value, PaymentRequestStatus::Closed->value])
            ->latest()
            ->get()
            ->filter(fn (PaymentRequest $request): bool => $request->matchesStudent($profile))
            ->values();
    }

    public function submit(PaymentRequest $request, User $student, array $data): PaymentSubmission
    {
        return DB::transaction(function () use ($request, $student, $data): PaymentSubmission {
            $locked = PaymentRequest::query()->whereKey($request->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isOpen()) {
                throw new \DomainException('This payment request is closed or past its due date.');
            }

            $profile = $student->studentProfile;
            if ($profile === null || ! $locked->matchesStudent($profile) || ! $student->canParticipate()) {
                throw new \DomainException('You are not eligible for this payment request.');
            }

            $existing = PaymentSubmission::query()
                ->where('payment_request_id', $locked->getKey())
                ->where('user_id', $student->getKey())
                ->lockForUpdate()
                ->first();

            if ($existing !== null && $existing->status !== PaymentSubmissionStatus::Rejected) {
                throw new \DomainException('Your payment confirmation is already being reviewed or has been approved.');
            }

            if ($existing !== null) {
                $existing->update([
                    ...$data,
                    'status' => PaymentSubmissionStatus::Pending,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_notes' => null,
                ]);

                $submission = $existing->fresh();
            } else {
                $submission = PaymentSubmission::create([
                    ...$data,
                    'payment_request_id' => $locked->getKey(),
                    'user_id' => $student->getKey(),
                    'status' => PaymentSubmissionStatus::Pending,
                ]);
            }

            $this->audit->log('payment_confirmation_submitted', $submission, null, [
                'payment_request_id' => $locked->getKey(),
                'amount_paid' => $submission->amount_paid,
            ]);

            return $submission;
        });
    }

    public function review(PaymentSubmission $submission, User $reviewer, PaymentSubmissionStatus $status, string $notes): PaymentSubmission
    {
        if ($status === PaymentSubmissionStatus::Pending) {
            throw new \DomainException('Choose approved or rejected when reviewing a payment.');
        }

        return DB::transaction(function () use ($submission, $reviewer, $status, $notes): PaymentSubmission {
            $locked = PaymentSubmission::query()->whereKey($submission->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== PaymentSubmissionStatus::Pending) {
                throw new \DomainException('This payment confirmation has already been reviewed.');
            }

            $locked->update([
                'status' => $status,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            $this->notifications->sendToUser(
                recipient: $locked->user,
                sender: $reviewer,
                title: 'Payment confirmation '.$status->label().': '.$locked->paymentRequest->title,
                message: $notes !== '' ? $notes : 'Your payment confirmation was '.$status->label().'.',
                priority: $status === PaymentSubmissionStatus::Approved ? Priority::Normal : Priority::High,
                related: $locked->paymentRequest,
                actionUrl: route('student.payments.show', $locked->paymentRequest),
            );

            $this->audit->log('payment_confirmation_reviewed', $locked, null, [
                'status' => $status->value,
                'reviewed_by' => $reviewer->getKey(),
            ]);

            return $locked->fresh(['user', 'reviewer', 'paymentRequest']);
        });
    }

    public function remind(PaymentRequest $request, User $sender): int
    {
        if ($request->status !== PaymentRequestStatus::Open) {
            throw new \DomainException('Reminders can only be sent for open payment requests.');
        }

        $alreadySubmitted = $request->submissions()
            ->whereIn('status', [
                PaymentSubmissionStatus::Pending->value,
                PaymentSubmissionStatus::Approved->value,
            ])
            ->pluck('user_id');

        $targets = $this->targetUsers($request)->reject(
            fn (User $user): bool => $alreadySubmitted->contains($user->getKey()),
        );

        foreach ($targets as $student) {
            $this->notifications->sendToUser(
                recipient: $student,
                sender: $sender,
                title: 'Payment reminder: '.$request->title,
                message: 'This is a reminder that TZS '.number_format((float) $request->amount, 2).' is due '.($request->due_at?->format('d M Y H:i') ?? 'as soon as possible').'.',
                priority: Priority::High,
                related: $request,
                actionUrl: route('student.payments.show', $request),
            );
        }

        $this->audit->log('payment_reminder_sent', $request, null, ['recipient_count' => $targets->count()]);

        return $targets->count();
    }

    public function close(PaymentRequest $request): PaymentRequest
    {
        if ($request->status !== PaymentRequestStatus::Open) {
            throw new \DomainException('This payment request is already closed.');
        }

        $request->update([
            'status' => PaymentRequestStatus::Closed,
            'closed_at' => now(),
        ]);

        $this->audit->log('payment_request_closed', $request, ['status' => PaymentRequestStatus::Open->value], ['status' => PaymentRequestStatus::Closed->value]);

        return $request->fresh();
    }

    private function targetUsers(PaymentRequest $request): Collection
    {
        $profiles = StudentProfile::query()
            ->with('user')
            ->where('status', StudentStatus::Active->value)
            ->where('verification_status', VerificationStatus::Verified->value)
            ->whereHas('user', fn ($query) => $query->where('account_status', 'active'))
            ->when($request->audience === 'college', fn ($query) => $query->where('college', $request->college))
            ->when($request->audience === 'programme', fn ($query) => $query->where('programme', $request->programme))
            ->when($request->audience === 'year', fn ($query) => $query->where('year_of_study', $request->year_of_study))
            ->when($request->audience === 'hostel', fn ($query) => $query->where('hostel', $request->hostel))
            ->get();

        return $profiles->pluck('user')->filter()->values();
    }
}
