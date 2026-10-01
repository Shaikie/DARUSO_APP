<x-app-layout>
    @section('title', 'New leadership assignment')
    @section('heading', 'New leadership assignment')
    @section('subheading', 'Assign a person to a position within a term.')

    @section('actions')
        <a href="{{ route('leader.leadership.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.leadership.store') }}">
                @csrf

                <x-page-card icon="person-badge" title="Assignment details">
                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-semibold">Leader</label>
                        <select id="user_id" name="user_id" required
                                class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">Choose a person…</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('user_id') == $candidate->id)>
                                    {{ $candidate->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="position_id" class="form-label fw-semibold">Position</label>
                        <select id="position_id" name="position_id" required
                                class="form-select @error('position_id') is-invalid @enderror">
                            <option value="">Choose a position…</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>
                                    {{ $position->name }} (level {{ $position->hierarchy_level }})
                                </option>
                            @endforeach
                        </select>
                        @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ministry_id" class="form-label fw-semibold">Ministry (optional)</label>
                        <select id="ministry_id" name="ministry_id"
                                class="form-select @error('ministry_id') is-invalid @enderror">
                            <option value="">No ministry (general leadership)</option>
                            @foreach ($ministries as $ministry)
                                <option value="{{ $ministry->id }}" @selected(old('ministry_id') == $ministry->id)>
                                    {{ $ministry->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ministry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Assigning a ministry gives the leader that ministry's complaints
                            and scoped document access.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="leadership_term_id" class="form-label fw-semibold">Leadership term</label>
                        <select id="leadership_term_id" name="leadership_term_id" required
                                class="form-select @error('leadership_term_id') is-invalid @enderror">
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}"
                                    @selected(old('leadership_term_id', $selectedTerm) == $term->id)>
                                    {{ $term->name }}{{ $term->is_active ? ' (active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('leadership_term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Create assignment
                    </button>
                </x-page-card>
            </form>
        </div>

        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <x-page-card icon="info-circle" title="Note">
                <p class="small text-muted mb-0">
                    A person cannot hold the same position twice in one term. The person
                    automatically gains a leader profile, which grants access to the
                    leadership area.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>