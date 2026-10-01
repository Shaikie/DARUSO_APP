<x-app-layout>
    @section('title', 'Edit leadership assignment')
    @section('heading', 'Edit leadership assignment')
    @section('subheading', $assignment->user?->name ?? 'Assignment')

    @section('actions')
        <a href="{{ route('leader.leadership.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.leadership.update', $assignment) }}">
                @csrf
                @method('PUT')

                <x-page-card icon="person-badge" title="Assignment details">
                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-semibold">Leader</label>
                        <select id="user_id" name="user_id" required
                                class="form-select @error('user_id') is-invalid @enderror">
                            @foreach (\App\Models\User::orderBy('name')->limit(500)->get() as $candidate)
                                <option value="{{ $candidate->id }}"
                                    @selected(old('user_id', $assignment->user_id) == $candidate->id)>
                                    {{ $candidate->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>

                    <div class="mb-3">
                        <label for="position_id" class="form-label fw-semibold">Position</label>
                        <select id="position_id" name="position_id" required
                                class="form-select @error('position_id') is-invalid @enderror">
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}"
                                    @selected(old('position_id', $assignment->position_id) == $position->id)>
                                    {{ $position->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>

                    <div class="mb-3">
                        <label for="ministry_id" class="form-label fw-semibold">Ministry</label>
                        <select id="ministry_id" name="ministry_id"
                                class="form-select @error('ministry_id') is-invalid @enderror">
                            <option value="">No ministry</option>
                            @foreach ($ministries as $ministry)
                                <option value="{{ $ministry->id }}"
                                    @selected(old('ministry_id', $assignment->ministry_id) == $ministry->id)>
                                    {{ $ministry->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ministry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="leadership_term_id" class="form-label fw-semibold">Leadership term</label>
                        <select id="leadership_term_id" name="leadership_term_id" required
                                class="form-select @error('leadership_term_id') is-invalid @enderror">
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}"
                                    @selected(old('leadership_term_id', $assignment->leadership_term_id) == $term->id)>
                                    {{ $term->name }}{{ $term->is_active ? ' (active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('leadership_term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>
            </form>
        </div>
    </div>
</x-app-layout>