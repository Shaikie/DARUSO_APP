{{--
    Student academic details, editable by the student themselves.

    Registration number and status are deliberately absent: they are
    administratively controlled, and the policy plus the Form Request restrict
    this form to descriptive fields so a student cannot escalate their own record.
--}}
<x-page-card icon="mortarboard" title="Student details">
    <form method="POST" action="{{ route('profile.student.update') }}">
        @csrf
        @method('PATCH')

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="college" class="form-label small fw-semibold">College</label>
                <input type="text" id="college" name="college" required
                       class="form-control @error('college') is-invalid @enderror"
                       value="{{ old('college', $studentProfile->college) }}">
                @error('college') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="school_faculty" class="form-label small fw-semibold">School / Faculty</label>
                <input type="text" id="school_faculty" name="school_faculty" required
                       class="form-control @error('school_faculty') is-invalid @enderror"
                       value="{{ old('school_faculty', $studentProfile->school_faculty) }}">
                @error('school_faculty') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="programme" class="form-label small fw-semibold">Programme</label>
                <input type="text" id="programme" name="programme" required
                       class="form-control @error('programme') is-invalid @enderror"
                       value="{{ old('programme', $studentProfile->programme) }}">
                @error('programme') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-6 col-md-3">
                <label for="year_of_study" class="form-label small fw-semibold">Year</label>
                <input type="number" id="year_of_study" name="year_of_study" required min="1" max="10"
                       class="form-control @error('year_of_study') is-invalid @enderror"
                       value="{{ old('year_of_study', $studentProfile->year_of_study) }}">
                @error('year_of_study') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-6 col-md-3">
                <label for="gender" class="form-label small fw-semibold">Gender</label>
                <input type="text" id="gender" name="gender" maxlength="20"
                       class="form-control @error('gender') is-invalid @enderror"
                       value="{{ old('gender', $studentProfile->gender) }}">
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="hostel" class="form-label small fw-semibold">Hostel</label>
                <input type="text" id="hostel" name="hostel"
                       class="form-control @error('hostel') is-invalid @enderror"
                       value="{{ old('hostel', $studentProfile->hostel) }}">
                @error('hostel') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Registration number</label>
                <input type="text" class="form-control" value="{{ $studentProfile->registration_number }}" disabled>
                <div class="form-text">
                    Contact the registrar if this is wrong — it cannot be edited here.
                </div>
            </div>
        </div>

        <button class="btn btn-primary mt-4">
            <i class="bi bi-check-lg me-1"></i>Save student details
        </button>
    </form>
</x-page-card>