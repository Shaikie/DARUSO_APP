<x-app-layout>
@section('title',$paymentRequest->title) @section('heading',$paymentRequest->title) @section('subheading','Private payment request management. Individual confirmation records are visible only to authorised payment managers.')
<div class="daruso-panel p-4 mb-4">
<div class="d-flex justify-content-between flex-wrap gap-2"><span class="badge {{ $paymentRequest->status->value==='open' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $paymentRequest->status->label() }}</span><strong class="fs-4">TZS {{ number_format((float)$paymentRequest->amount,2) }}</strong></div>
<p class="mt-3">{{ $paymentRequest->description }}</p><p class="small text-muted">Audience: {{ ucfirst($paymentRequest->audience) }} · Due: {{ $paymentRequest->due_at?->format('d M Y H:i') ?? 'No due date' }}</p>
@if($paymentRequest->instructions)<h2 class="h6">Payment instructions</h2><p>{{ $paymentRequest->instructions }}</p>@endif
<div class="d-flex flex-wrap gap-2 mt-3">@if($paymentRequest->status->value==='open')<form method="POST" action="{{ route('leader.payments.remind',$paymentRequest) }}">@csrf<button class="btn btn-outline-primary">Send private reminders</button></form><form method="POST" action="{{ route('leader.payments.close',$paymentRequest) }}">@csrf<button class="btn btn-outline-danger">Close request</button></form>@endif<a href="{{ route('leader.payments.index') }}" class="btn btn-light">Back</a></div>
</div>
<div class="daruso-panel"><div class="p-3 border-bottom"><h2 class="h6 mb-1">Submitted confirmations</h2><small class="text-muted">{{ $paymentRequest->submissions->count() }} private record(s). Students cannot see this list.</small></div>
@forelse($paymentRequest->submissions as $submission)
<article class="p-3 border-bottom"><div class="d-flex justify-content-between flex-wrap gap-2"><div><strong>{{ $submission->user?->name ?? 'Account unavailable' }}</strong><small class="d-block text-muted">{{ $submission->created_at->format('d M Y H:i') }} · TZS {{ number_format((float)$submission->amount_paid,2) }}</small></div><span class="badge {{ $submission->status->badgeClass() }}">{{ $submission->status->label() }}</span></div>
<p class="small mt-2 mb-1">Reference: <code>{{ $submission->transaction_reference }}</code></p>@if($submission->notes)<p class="small mb-2">{{ $submission->notes }}</p>@endif
@if($submission->status->value==='pending')<form method="POST" action="{{ route('leader.payments.review',[$paymentRequest,$submission]) }}" class="row g-2 mt-2">@csrf<div class="col-md-3"><select class="form-select" name="status"><option value="approved">Approve</option><option value="rejected">Reject</option></select></div><div class="col-md-6"><input class="form-control" name="review_notes" placeholder="Optional review note"></div><div class="col-md-3"><button class="btn btn-primary w-100">Submit review</button></div></form>@elseif($submission->review_notes)<p class="small text-muted mb-0">Review note: {{ $submission->review_notes }}</p>@endif
</article>
@empty<p class="p-4 text-muted">No students have submitted a confirmation yet. No unpaid-student list is displayed.</p>@endforelse
</div>
</x-app-layout>