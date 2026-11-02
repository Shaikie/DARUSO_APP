<x-app-layout>
@section('title',$paymentRequest->title) @section('heading',$paymentRequest->title) @section('subheading','Private payment request and your own confirmation status.')
<div class="daruso-panel p-4 mb-4">
<div class="d-flex justify-content-between flex-wrap gap-2"><span class="badge {{ $paymentRequest->status->value==='open' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $paymentRequest->status->label() }}</span><strong class="fs-4">TZS {{ number_format((float)$paymentRequest->amount,2) }}</strong></div>
@if($paymentRequest->description)<p class="mt-3">{{ $paymentRequest->description }}</p>@endif
<p class="small text-muted mb-2">Due: {{ $paymentRequest->due_at?->format('d M Y H:i') ?? 'No due date' }}</p>
@if($paymentRequest->instructions)<hr><h2 class="h6">Payment instructions</h2><p class="mb-0">{{ $paymentRequest->instructions }}</p>@endif
</div>
@if($submission)
<div class="daruso-panel p-4 mb-4"><div class="d-flex justify-content-between"><h2 class="h6">Your payment confirmation</h2><span class="badge {{ $submission->status->badgeClass() }}">{{ $submission->status->label() }}</span></div>
<p class="mb-1">Amount submitted: TZS {{ number_format((float)$submission->amount_paid,2) }}</p><p class="mb-1">Reference: <code>{{ $submission->transaction_reference }}</code></p>
@if($submission->notes)<p class="mb-1">{{ $submission->notes }}</p>@endif
@if($submission->review_notes)<div class="alert alert-light mt-3 mb-0"><strong>Review note:</strong> {{ $submission->review_notes }}</div>@endif
@if($submission->status->value==='pending')<p class="small text-muted mt-3 mb-0">Your confirmation is awaiting private review by DARUSO leadership.</p>@endif
</div>
@endif
@if($paymentRequest->isOpen() && (!$submission || $submission->status->value==='rejected'))
<div class="daruso-panel p-4"><h2 class="h6">{{ $submission ? 'Resubmit payment confirmation' : 'Submit payment confirmation' }}</h2><p class="small text-muted">Make the payment using the instructions above, then enter the transaction reference. This form records your confirmation; it does not process a payment.</p>
<form method="POST" action="{{ route('student.payments.submit',$paymentRequest) }}">@csrf
<label class="form-label">Amount paid (TZS)</label><input class="form-control mb-3" type="number" name="amount_paid" min="0.01" max="{{ $paymentRequest->amount }}" step="0.01" value="{{ old('amount_paid',$paymentRequest->amount) }}" required>
<label class="form-label">Transaction reference</label><input class="form-control mb-3" name="transaction_reference" maxlength="120" value="{{ old('transaction_reference') }}" required>
<label class="form-label">Additional note (optional)</label><textarea class="form-control mb-3" name="notes" rows="3">{{ old('notes') }}</textarea>
<button class="btn btn-primary">Submit privately</button>
</form></div>
@endif
<a href="{{ route('student.payments.index') }}" class="btn btn-light mt-3">Back to my payments</a>
</x-app-layout>