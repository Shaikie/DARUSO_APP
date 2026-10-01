{{-- Account contact details. --}}
<x-page-card icon="person" title="Account information">
    <p class="small text-muted">
        Update your name, email address or phone number. Changing your email requires
        re-verification.
    </p>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="name" class="form-label small fw-semibold">Name</label>
                <input type="text" id="name" name="name" required autocomplete="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="phone" class="form-label small fw-semibold">Phone</label>
                <input type="tel" id="phone" name="phone" maxlength="32" autocomplete="tel"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $user->phone) }}">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror>
            </div>

            <div class="col-12">
                <label for="email" class="form-label small fw-semibold">Email</label>
                <input type="email" id="email" name="email" required autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="alert alert-warning small mt-2 mb-0" role="alert">
                        Your email address is unverified.
                        <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                            Re-send the verification email
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-1">A new verification link has been sent.</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <button class="btn btn-primary mt-4">
            <i class="bi bi-check-lg me-1"></i>Save
        </button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small ms-3">Saved.</span>
        @endif
    </form>
</x-page-card>