@section('title', 'Sign in')

<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Sign in</h2>
    <p class="text-muted small mb-4">Use your DARUSO account to continue.</p>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" value="1" class="form-check-input"
                       @checked(old('remember'))>
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>

            <a href="{{ route('password.request') }}" class="small text-decoration-none">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i>Sign in
        </button>

        <p class="text-center small mt-3 mb-0">
            No account yet?
            <a href="{{ route('register') }}" class="text-decoration-none">Register</a>
        </p>
    </form>
</x-guest-layout>