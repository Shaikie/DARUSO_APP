<x-app-layout>
@section('title','Payment requests') @section('heading','Payment requests') @section('subheading','Create contribution requests, send private reminders and review individual confirmations. Student payment status is never published to students.')
@section('actions')<a href="{{ route('leader.payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New payment request</a>@endsection
<div class="daruso-panel">
@forelse($requests as $paymentRequest)
<a href="{{ route('leader.payments.show',$paymentRequest) }}" class="daruso-list-item text-decoration-none"><span class="daruso-list-icon"><i class="bi bi-wallet2"></i></span><span class="flex-grow-1"><strong>{{ $paymentRequest->title }}</strong><small>TZS {{ number_format((float)$paymentRequest->amount,2) }} · {{ ucfirst($paymentRequest->audience) }} · {{ $paymentRequest->submissions_count }} confirmation(s)</small></span><span class="badge {{ $paymentRequest->status->value==='open' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $paymentRequest->status->label() }}</span></a>
@empty<p class="p-4 text-muted">No payment requests have been created.</p>@endforelse
<div class="p-3">{{ $requests->links() }}</div>
</div>
</x-app-layout>