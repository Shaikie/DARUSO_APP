{{-- Password change. Requires the current password and re-authentication. --}}
<x-page-card icon="key" title="Password">
    <p class="small text-muted">
        Use a long, random password. You will be signed out of other sessions only after
        you change it here.
    </p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-12">
                <label for="update_password_current_password" class="form-label small fw-semibold">
                    Current password
                </label>
                <input type="password" id="update_password_current_password" name="current_password"
                       autocomplete="current-password"
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="update_password_password" class="form-label small fw-semibold">
                    New password
                </label>
                <input type="password" id="update_password_password" name="password"
                       autocomplete="new-password"
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="update_password_password_confirmation" class="form-label small fw-semibold">
                    Confirm password
                </label>
                <input type="password" id="update_password_password_confirmation"
                       name="password_confirmation" autocomplete="new-password"
                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
                @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button class="btn btn-primary mt-4">
            <i class="bi bi-check-lg me-1"></i>Update password
        </button>
    </form>
</x-page-card>