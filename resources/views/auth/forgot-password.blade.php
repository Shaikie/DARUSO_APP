@section('title', 'Forgot password')

<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Forgot your password?</h2>
    <p class="text-muted small mb-4">
        Enter your email address and we will send a link to reset your password.
    </p>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label small fw-semibold">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-envelope me-1"></i>Email password reset link
        </button>

        <p class="text-center small mt-3 mb-0">
            <a href="{{ route('login') }}" class="text-decoration-none">Back to sign in</a>
        </p>
    </form>
</x-guest-layout>