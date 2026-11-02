<x-app-layout>
@section('title','Payments & contributions') @section('heading','Payments & contributions') @section('subheading','Payment requests addressed to you. Your submission status is private to your account.')
<div class="daruso-panel">
@forelse($requests as $paymentRequest)
@php($submission = $submissions->get($paymentRequest->id))
<a href="{{ route('student.payments.show',$paymentRequest) }}" class="daruso-list-item text-decoration-none">
<span class="daruso-list-icon"><i class="bi bi-wallet2"></i></span>
<span class="flex-grow-1"><strong>{{ $paymentRequest->title }}</strong><small>TZS {{ number_format((float)$paymentRequest->amount,2) }} · Due {{ $paymentRequest->due_at?->format('d M Y') ?? 'No due date' }}{{ $paymentRequest->due_at?->isPast() ? ' · Past due' : '' }}</small></span>
@if($submission)<span class="badge {{ $submission->status->badgeClass() }}">{{ $submission->status->label() }}</span>@else<span class="badge text-bg-warning">Action required</span>@endif
</a>
@empty
<div class="daruso-empty py-5 text-center"><i class="bi bi-wallet2 fs-2"></i><h2 class="h6 mt-2">No payment requests</h2><p class="text-muted small">Payment requests addressed to you will appear here.</p></div>
@endforelse
</div>
</x-app-layout>