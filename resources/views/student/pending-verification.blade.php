<x-app-layout>
    @php
        $isPending = $status === \App\\Enums\\StudentStatus::PendingVerification;
        $title = $isPending ? 'Your student record is under review' : 'Student services are unavailable';
        $description = match ($status) {
            \App\\Enums\\StudentStatus::PendingVerification => 'Student services will become available after your academic details have been verified.',
            \App\\Enums\\StudentStatus::Suspended => 'Your student access has been suspended. Contact the DARUSO office for clarification.',
            \App\\Enums\\StudentStatus::Graduated => 'This student record is marked as graduated, so current-student services are no longer available.',
            \App\\Enums\\StudentStatus::Expelled => 'This student record is not eligible for current-student services. Contact the authorised university office if you believe this is incorrect.',
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
                                A DARUSO administrator or authorised student-services leader must review
                                your registration number and academic information before your account is
                                activated for student services.
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
