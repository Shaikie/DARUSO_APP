<?php

namespace App\Http\Controllers;

use App\Enums\PaymentSubmissionStatus;
use App\Models\PaymentRequest;
use App\Models\PaymentSubmission;
use App\Services\PaymentRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentRequestService $payments,
    ) {}

    private function requirePaymentManager(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('payment.manage'), 403);
    }

    public function studentIndex(Request $request): View
    {
        $requests = $this->payments->visibleTo($request->user());
        $submissions = PaymentSubmission::query()
            ->where('user_id', $request->user()->getKey())
            ->whereIn('payment_request_id', $requests->pluck('id'))
            ->get()
            ->keyBy('payment_request_id');

        return view('student.payments.index', compact('requests', 'submissions'));
    }

    public function studentShow(Request $request, PaymentRequest $paymentRequest): View
    {
        $student = $request->user();
        abort_unless($student->canParticipate() && $student->studentProfile !== null
            && $paymentRequest->matchesStudent($student->studentProfile), 404);

        $submission = $paymentRequest->submissions()
            ->where('user_id', $student->getKey())
            ->first();

        return view('student.payments.show', compact('paymentRequest', 'submission'));
    }

    public function studentSubmit(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $data = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01', 'max:'.$paymentRequest->amount],
            'transaction_reference' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->payments->submit($paymentRequest, $request->user(), $data);
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->route('student.payments.show', $paymentRequest)
            ->with('success', 'Your payment confirmation has been submitted privately for review.');
    }

    public function leaderIndex(Request $request): View
    {
        $this->requirePaymentManager($request);
        $requests = PaymentRequest::query()
            ->with('creator')
            ->withCount('submissions')
            ->latest()
            ->paginate(15);

        return view('leader.payments.index', compact('requests'));
    }

    public function leaderCreate(Request $request): View
    {
        $this->requirePaymentManager($request);

        return view('leader.payments.create');
    }

    public function leaderStore(Request $request): RedirectResponse
    {
        $this->requirePaymentManager($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date', 'after:now'],
            'audience' => ['required', Rule::in(['all', 'college', 'programme', 'year', 'hostel'])],
            'college' => ['nullable', 'required_if:audience,college', 'string', 'max:180'],
            'programme' => ['nullable', 'required_if:audience,programme', 'string', 'max:180'],
            'year_of_study' => ['nullable', 'required_if:audience,year', 'integer', 'min:1', 'max:8'],
            'hostel' => ['nullable', 'required_if:audience,hostel', 'string', 'max:180'],
        ]);

        try {
            $paymentRequest = $this->payments->create($data, $request->user());
        } catch (\DomainException $exception) {
            return back()->withInput()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->route('leader.payments.show', $paymentRequest)
            ->with('success', 'Payment request created and private reminders sent to the selected student audience.');
    }

    public function leaderShow(Request $request, PaymentRequest $paymentRequest): View
    {
        $this->requirePaymentManager($request);
        $paymentRequest->load([
            'creator',
            'submissions' => fn ($query) => $query->with(['user.studentProfile', 'reviewer'])->latest(),
        ]);

        return view('leader.payments.show', compact('paymentRequest'));
    }

    public function leaderClose(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $this->requirePaymentManager($request);

        try {
            $this->payments->close($paymentRequest);
        } catch (\DomainException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return back()->with('success', 'Payment request closed.');
    }

    public function leaderRemind(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $this->requirePaymentManager($request);

        try {
            $count = $this->payments->remind($paymentRequest, $request->user());
        } catch (\DomainException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return back()->with('success', $count.' private reminder(s) sent.');
    }

    public function leaderReview(Request $request, PaymentRequest $paymentRequest, PaymentSubmission $submission): RedirectResponse
    {
        $this->requirePaymentManager($request);
        abort_unless($submission->payment_request_id === $paymentRequest->getKey(), 404);
        $data = $request->validate([
            'status' => ['required', Rule::in([PaymentSubmissionStatus::Approved->value, PaymentSubmissionStatus::Rejected->value])],
            'review_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            $this->payments->review(
                $submission,
                $request->user(),
                PaymentSubmissionStatus::from($data['status']),
                $data['review_notes'] ?? '',
            );
        } catch (\DomainException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return back()->with('success', 'Payment confirmation reviewed.');
    }
}
