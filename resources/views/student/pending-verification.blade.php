<x-app-layout>
    @php
        $statusValue = $status?->value;
        $verificationValue = $verificationStatus?->value ?? null;
        $isPending = in_array($statusValue, ['pending', 'pending_verification'], true)
            || $verificationValue === 'pending';
        $title = $isPending ? 'Your student record is under review' : 'Student services are unavailable';
        $description = match ($statusValue) {
            'pending', 'pending_verification' => 'Student services will become available after your academic details have been verified.',
            'active' => 'Your academic details are awaiting verification before student services can be enabled.',
            'suspended' => 'Your student access has been suspended. Contact the DARUSO office for clarification.',
            'graduated' => 'This student record is marked as graduated, so current-student services are no longer available.',
            'expelled' => 'This student record is not eligible for current-student services. Contact the authorised university office if you believe this is incorrect.',
            default => 'Your student record needs attention before student services can be accessed.',
        };
    @endphp

    @section('title', 'Student account status')
    @section('heading', $title)
    @section('subheading', $description)

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <x-page-card icon="shield-check" title="{{ $isPending ? 'Verification pending' : 'Account status' }}">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-light text-primary p-3">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div>
                        @if ($isPending)
                            <p class="mb-2">Your registration details have been submitted successfully.</p>
                            <p class="text-muted small mb-3">
                                An authorised DARUSO leader must review your registration number and
                                academic information before student services become available.
                            </p>
                        @else
                            <p class="mb-2">Your current student record status restricts access to student services.</p>
                            <p class="text-muted small mb-3">{{ $description }}</p>
                        @endif
                        <span class="badge {{ $status?->badgeClass() ?? 'text-bg-secondary' }}">
                            {{ $status?->label() ?? 'Status unavailable' }}
                        </span>
                    </div>
                </div>
                <hr>
                <p class="small text-muted mb-0">
                    If you believe this status is incorrect or needs review, contact the DARUSO office
                    and provide the email address associated with your account.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>
