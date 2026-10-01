<x-app-layout>
    @section('title', 'Settings')
    @section('heading', 'System settings')
    @section('subheading', 'Configurable values used across the platform.')

    @section('actions')
        <a href="{{ route('leader.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to dashboard
        </a>
    @endsection

    <form method="POST" action="{{ route('leader.settings.update') }}">
        @csrf
        @method('PUT')

        <x-page-card icon="gear" title="Settings">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="system_name" class="form-label fw-semibold">System name</label>
                    <input type="text" id="system_name" name="system_name" required maxlength="100"
                           class="form-control @error('system_name') is-invalid @enderror"
                           value="{{ old('system_name', $values['system_name'] ?? 'DARUSO') }}">
                    @error('system_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="system_tagline" class="form-label fw-semibold">Tagline</label>
                    <input type="text" id="system_tagline" name="system_tagline" maxlength="255"
                           class="form-control @error('system_tagline') is-invalid @enderror"
                           value="{{ old('system_tagline', $values['system_tagline'] ?? '') }}">
                    @error('system_tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="organisation_name" class="form-label fw-semibold">Organisation name</label>
                    <input type="text" id="organisation_name" name="organisation_name" maxlength="255"
                           class="form-control @error('organisation_name') is-invalid @enderror"
                           value="{{ old('organisation_name', $values['organisation_name'] ?? '') }}">
                    @error('organisation_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="institution_name" class="form-label fw-semibold">Institution name</label>
                    <input type="text" id="institution_name" name="institution_name" maxlength="255"
                           class="form-control @error('institution_name') is-invalid @enderror"
                           value="{{ old('institution_name', $values['institution_name'] ?? '') }}">
                    @error('institution_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="default_announcement_priority" class="form-label fw-semibold">
                        Default announcement priority
                    </label>
                    <select id="default_announcement_priority" name="default_announcement_priority"
                            class="form-select @error('default_announcement_priority') is-invalid @enderror">
                        @foreach (\App\Enums\Priority::cases() as $priority)
                            <option value="{{ $priority->value }}"
                                @selected(old('default_announcement_priority', $values['default_announcement_priority'] ?? 'normal') === $priority->value)>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('default_announcement_priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="complaint_response_days" class="form-label fw-semibold">
                        Complaint response window (days)
                    </label>
                    <input type="number" id="complaint_response_days" name="complaint_response_days"
                           min="1" max="365"
                           class="form-control @error('complaint_response_days') is-invalid @enderror"
                           value="{{ old('complaint_response_days', $values['complaint_response_days'] ?? 14) }}">
                    @error('complaint_response_days') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                </div>
            </div>

            <hr>

            <h3 class="h6 fw-semibold mb-3">Feature toggles</h3>

            @foreach ([
                'allow_public_announcements' => 'Allow announcements with public visibility',
                'allow_student_registration' => 'Allow self-registration of new students',
                'notification_email_enabled' => 'Email notification channel',
                'notification_sms_enabled' => 'SMS notification channel',
            ] as $key => $label)
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1"
                           id="{{ $key }}"
                           @checked(old($key, (bool) ($values[$key] ?? false)))>
                    <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                </div>
            @endforeach

            <p class="small text-muted">
                The email and SMS channels are recorded here so the delivery layer can be
                enabled later without changing business logic. No external provider is
                configured, so enabling them has no effect yet.
            </p>

            <button class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Save settings
            </button>
        </x-page-card>
    </form>
</x-app-layout>