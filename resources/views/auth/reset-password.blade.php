@section('title', 'Reset password')

<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Choose a new password</h2>
    <p class="text-muted small mb-4">Use a long, random password.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" required
                   autocomplete="username" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">New password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   autocomplete="new-password"
                   class="form-control @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-lg me-1"></i>Reset password
        </button>
    </form>
</x-guest-layout>