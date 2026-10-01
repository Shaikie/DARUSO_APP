@section('title', 'Register')

<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Create your account</h2>
    <p class="text-muted small mb-4">Register to receive announcements and submit complaints.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label small fw-semibold">Full name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                   autocomplete="name" class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   autocomplete="username" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror>
            <div class="form-text">At least 8 characters.</div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   autocomplete="new-password"
                   class="form-control @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-person-plus me-1"></i>Register
        </button>

        <p class="text-center small mt-3 mb-0">
            Already registered?
            <a href="{{ route('login') }}" class="text-decoration-none">Sign in</a>
        </p>
    </form>
</x-guest-layout>