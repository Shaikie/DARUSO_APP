@section('title', 'Confirm password')

<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Confirm your password</h2>
    <p class="text-muted small mb-4">
        This is a secure area. Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" id="password" name="password" required autofocus
                   autocomplete="current-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-lg me-1"></i>Confirm
        </button>
    </form>
</x-guest-layout>