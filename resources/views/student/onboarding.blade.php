<x-app-layout>
    @section('title', 'Welcome')
    @section('heading', 'Welcome, '.\Illuminate\Support\Str::before(auth()->user()->name, ' '))
    @section('subheading', 'Complete your student record to finish setting up your account.')

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <x-page-card icon="mortarboard" title="Student details">
                <p class="small text-muted">
                    Your account is active but not yet linked to a student record. Supply your
                    academic details below. Registration numbers are verified by the
                    registrar, so a mismatch may be corrected later by a leader.
                </p>

                <form method="POST" action="{{ route('student.profile.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="registration_number" class="form-label small fw-semibold">
                                Registration number
                            </label>
                            <input type="text" id="registration_number" name="registration_number" required
                                   class="form-control @error('registration_number') is-invalid @enderror"
                                   value="{{ old('registration_number') }}" placeholder="REG-2026-0001">
                            @error('registration_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="year_of_study" class="form-label small fw-semibold">Year of study</label>
                            <input type="number" id="year_of_study" name="year_of_study" required
                                   min="1" max="10"
                                   class="form-control @error('year_of_study') is-invalid @enderror"
                                   value="{{ old('year_of_study', 1) }}">
                            @error('year_of_study') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>

                        <div class="col-12">
                            <label for="college" class="form-label small fw-semibold">College</label>
                            <input type="text" id="college" name="college" required
                                   class="form-control @error('college') is-invalid @enderror"
                                   value="{{ old('college') }}">
                            @error('college') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>

                        <div class="col-12">
                            <label for="school_faculty" class="form-label small fw-semibold">
                                School / Faculty
                            </label>
                            <input type="text" id="school_faculty" name="school_faculty" required
                                   class="form-control @error('school_faculty') is-invalid @enderror"
                                   value="{{ old('school_faculty') }}">
                            @error('school_faculty') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>

                        <div class="col-12">
                            <label for="programme" class="form-label small fw-semibold">Programme</label>
                            <input type="text" id="programme" name="programme" required
                                   class="form-control @error('programme') is-invalid @enderror"
                                   value="{{ old('programme') }}">
                            @error('programme') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>

                        <div class="col-6">
                            <label for="hostel" class="form-label small fw-semibold">Hostel</label>
                            <input type="text" id="hostel" name="hostel"
                                   class="form-control @error('hostel') is-invalid @enderror"
                                   value="{{ old('hostel') }}">
                            @error('hostel') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>

                        <div class="col-6">
                            <label for="gender" class="form-label small fw-semibold">Gender</label>
                            <input type="text" id="gender" name="gender" maxlength="20"
                                   class="form-control @error('gender') is-invalid @enderror"
                                   value="{{ old('gender') }}">
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-4">
                        <i class="bi bi-check-lg me-1"></i>Save and continue
                    </button>
                </form>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-5">
            <x-page-card icon="info-circle" title="Why this matters">
                <p class="small text-muted mb-0">
                    Announcements and events are targeted by college, programme, year of study
                    and hostel. Until your record is complete, leadership messages addressed to
                    those groups will not reach you.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>