<x-app-layout>
    @section('title', $student->user?->name ?? 'Student')
    @section('heading', $student->user?->name ?? 'Student')
    @section('subheading', $student->programme.' · Year '.$student->year_of_study)

    @section('actions')
        <a href="{{ route('leader.students.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        @can('update', $student)
            <a href="{{ route('leader.students.edit', $student) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
        @can('delete', $student)
            <form method="POST" action="{{ route('leader.students.destroy', $student) }}"
                  onsubmit="return confirm('Delete this student account and all related records?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="person-vcard" title="Academic record">
                <dl class="row mb-0">
                    {{--
                        Each sensitive field is gated individually. A leader may
                        legitimately see the programme without seeing the
                        registration number or hostel allocation.
                    --}}
                    @can('viewSensitive', $student)
                        <dt class="col-5 text-muted fw-normal">Registration number</dt>
                        <dd class="col-7">{{ $student->registration_number }}</dd>
                    @endcan

                    <dt class="col-5 text-muted fw-normal">College</dt>
                    <dd class="col-7">{{ $student->college }}</dd>

                    <dt class="col-5 text-muted fw-normal">School / Faculty</dt>
                    <dd class="col-7">{{ $student->school_faculty }}</dd>

                    <dt class="col-5 text-muted fw-normal">Programme</dt>
                    <dd class="col-7">{{ $student->programme }}</dd>

                    <dt class="col-5 text-muted fw-normal">Year of study</dt>
                    <dd class="col-7">{{ $student->year_of_study }}</dd>

                    @can('viewHostel', $student)
                        <dt class="col-5 text-muted fw-normal">Hostel</dt>
                        <dd class="col-7">{{ $student->hostel ?? 'Not allocated' }}</dd>
                    @endcan

                    <dt class="col-5 text-muted fw-normal">Gender</dt>
                    <dd class="col-7">{{ ucfirst($student->gender ?? '—') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Status</dt>
                    <dd class="col-7"><x-status-badge :status="$student->status" /></dd>
                </dl>

                @unless (auth()->user()->can('viewSensitive', $student))
                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-lock me-1"></i>
                        Registration number and contact details are hidden. They require the
                        <code>student.view_sensitive</code> permission.
                    </p>
                @endunless
            </x-page-card>

            <x-page-card class="mt-4" icon="exclamation-triangle" title="Recent complaints">
                @if ($complaints->isEmpty())
                    <p class="text-muted small mb-0">This student has not submitted any complaints.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($complaints as $complaint)
                            <li class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <span class="fw-semibold small">{{ $complaint->title }}</span>
                                    <div class="small text-muted">
                                        {{ $complaint->assignedMinistry?->name ?? 'Unassigned' }}
                                        · {{ $complaint->created_at->format('d M Y') }}
                                    </div>
                                </div>
                                <x-status-badge :status="$complaint->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="info-circle" title="Account">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Name</dt>
                    <dd class="col-7">{{ $student->user?->name ?? '—' }}</dd>

                    @can('viewSensitive', $student)
                        <dt class="col-5 text-muted fw-normal">Email</dt>
                        <dd class="col-7">{{ $student->user?->email ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Phone</dt>
                        <dd class="col-7">{{ $student->user?->phone ?? '—' }}</dd>
                    @endcan

                    <dt class="col-5 text-muted fw-normal">Registered</dt>
                    <dd class="col-7">{{ $student->created_at->format('d M Y') }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>