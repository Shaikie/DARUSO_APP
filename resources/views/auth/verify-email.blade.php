@section('title', 'Verify email')

<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Verify your email address</h2>

    <p class="text-muted small mb-4">
        We sent a verification link to
        <strong>{{ auth()->user()?->email }}</strong>. Follow the link in that message to
        finish setting up your account.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success small">
            A fresh verification link has been sent to your email address.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-envelope me-1"></i>Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-2">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">Sign out</button>
    </form>
</x-guest-layout>