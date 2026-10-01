<x-app-layout>
    @section('title', 'Add student')
    @section('heading', 'Add student')
    @section('subheading', 'Create an account and student record.')

    @section('actions')
        <a href="{{ route('leader.students.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.students.store') }}">
                @csrf

                <x-page-card icon="person-vcard" title="Account and academic record">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label fw-semibold">Full name</label>
                            <input type="text" id="name" name="name" required maxlength="255"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" id="email" name="email" required
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="registration_number" class="form-label fw-semibold">Registration number</label>
                            <input type="text" id="registration_number" name="registration_number" required
                                   class="form-control @error('registration_number') is-invalid @enderror"
                                   value="{{ old('registration_number') }}" placeholder="REG-2026-0001">
                            @error('registration_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="year_of_study" class="form-label fw-semibold">Year of study</label>
                            <input type="number" id="year_of_study" name="year_of_study" required
                                   min="1" max="10"
                                   class="form-control @error('year_of_study') is-invalid @enderror"
                                   value="{{ old('year_of_study', 1) }}">
                            @error('year_of_study') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="college" class="form-label fw-semibold">College</label>
                        <input type="text" id="college" name="college" required
                               class="form-control @error('college') is-invalid @enderror"
                               value="{{ old('college') }}">
                        @error('college') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="school_faculty" class="form-label fw-semibold">School / Faculty</label>
                        <input type="text" id="school_faculty" name="school_faculty" required
                               class="form-control @error('school_faculty') is-invalid @enderror"
                               value="{{ old('school_faculty') }}">
                        @error('school_faculty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="programme" class="form-label fw-semibold">Programme</label>
                        <input type="text" id="programme" name="programme" required
                               class="form-control @error('programme') is-invalid @enderror"
                               value="{{ old('programme') }}">
                        @error('programme') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label for="hostel" class="form-label fw-semibold">Hostel</label>
                            <input type="text" id="hostel" name="hostel"
                                   class="form-control @error('hostel') is-invalid @enderror"
                                   value="{{ old('hostel') }}">
                            @error('hostel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="gender" class="form-label fw-semibold">Gender</label>
                            <input type="text" id="gender" name="gender" maxlength="20"
                                   class="form-control @error('gender') is-invalid @enderror"
                                   value="{{ old('gender') }}">
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select id="status" name="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                @foreach (\App\Enums\StudentStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Create student record
                    </button>
                </x-page-card>
            </form>
        </div>

        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <x-page-card icon="info-circle" title="After creating">
                <p class="small text-muted mb-0">
                    An account is created with a temporary password. Ask the student to use
                    the "forgot password" flow to set their own before first sign-in.
                    Registration numbers must be unique system-wide.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>