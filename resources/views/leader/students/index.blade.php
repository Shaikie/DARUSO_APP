<x-app-layout>
    @section('title', 'Students')
    @section('heading', 'Students')
    @section('subheading', 'Student records, filtered by your permissions.')

    @section('actions')
        @can('create', App\Models\StudentProfile::class)
            <a href="{{ route('leader.students.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Add student
            </a>
        @endcan
    @endsection

    @unless (auth()->user()->can('viewSensitive', new \App\Models\StudentProfile))
        <div class="alert alert-warning d-flex gap-2" role="alert">
            <i class="bi bi-shield-exclamation mt-1"></i>
            <div class="small">
                You can view student names and academic details, but not registration
                numbers or contact details. Those require the
                <code>student.view_sensitive</code> permission.
            </div>
        </div>
    @endunless

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}"
                           placeholder="{{ auth()->user()->can('viewSensitive', new \App\Models\StudentProfile) ? 'Name, registration number or programme' : 'Name or programme' }}">
                </div>
                <div class="col-6 col-md-2">
                    <label for="college" class="form-label small fw-semibold">College</label>
                    <select id="college" name="college" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($filters['colleges'] as $college)
                            <option value="{{ $college }}" @selected(request('college') === $college)>
                                {{ $college }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="programme" class="form-label small fw-semibold">Programme</label>
                    <select id="programme" name="programme" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($filters['programmes'] as $programme)
                            <option value="{{ $programme }}" @selected(request('programme') === $programme)>
                                {{ $programme }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="year_of_study" class="form-label small fw-semibold">Year</label>
                    <select id="year_of_study" name="year_of_study" class="form-select form-select-sm">
                        <option value="">All</option>
                        @for ($year = 1; $year <= 6; $year++)
                            <option value="{{ $year }}" @selected((int) request('year_of_study') === $year)>
                                Year {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label small fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['search', 'college', 'programme', 'year_of_study', 'status']))
                        <a href="{{ route('leader.students.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="people" title="Student records">
        @if ($students->isEmpty())
            <x-empty-state icon="people"
                           title="No students match"
                           description="Adjust the filters or add a student record." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Student</th>
                            @can('viewSensitive', new \App\Models\StudentProfile)
                                <th scope="col">Registration number</th>
                            @endcan
                            <th scope="col">Programme</th>
                            <th scope="col">Year</th>
                            @can('viewHostel', new \App\Models\StudentProfile)
                                <th scope="col">Hostel</th>
                            @endcan
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.students.show', $student) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $student->user?->name ?? '—' }}
                                    </a>
                                    <div class="small text-muted">{{ $student->college }}</div>
                                </td>
                                @can('viewSensitive', $student)
                                    <td class="small">{{ $student->registration_number }}</td>
                                @endcan
                                <td class="small">{{ $student->programme }}</td>
                                <td class="small">{{ $student->year_of_study }}</td>
                                @can('viewHostel', $student)
                                    <td class="small">{{ $student->hostel ?? '—' }}</td>
                                @endcan
                                <td><x-status-badge :status="$student->status" /></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('leader.students.show', $student) }}"
                                           class="btn btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $student)
                                            <a href="{{ route('leader.students.edit', $student) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="mt-3">{{ $students->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>