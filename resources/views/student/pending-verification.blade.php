<x-app-layout>
    @section('title', 'Student verification')
    @section('heading', 'Your student record is under review')
    @section('subheading', 'Student services will become available after your academic details have been verified.')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <x-page-card icon="hourglass-split" title="Verification pending">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-light text-primary p-3">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div>
                        <p class="mb-2">Your registration details have been submitted successfully.</p>
                        <p class="text-muted small mb-3">
                            A DARUSO administrator or authorised student-services leader must review
                            your registration number and academic information before your account is
                            activated for student services.
                        </p>
                        <span class="badge text-bg-secondary">{{ $status?->label() ?? 'Pending verification' }}</span>
                    </div>
                </div>
                <hr>
                <p class="small text-muted mb-0">
                    If you believe this is taking too long, contact the DARUSO office and provide
                    the email address associated with your account.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>
