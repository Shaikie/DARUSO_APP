<x-app-layout>
    @section('title', 'Edit leadership term')
    @section('heading', 'Edit leadership term')
    @section('subheading', $term->name)

    @section('actions')
        <a href="{{ route('leader.leadership-terms.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.leadership-terms.update', $term) }}">
                @csrf
                @method('PUT')

                <x-page-card icon="calendar-range" title="Term details">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $term->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="start_date" class="form-label fw-semibold">Start date</label>
                            <input type="date" id="start_date" name="start_date" required
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', $term->start_date->toDateString()) }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="end_date" class="form-label fw-semibold">End date</label>
                            <input type="date" id="end_date" name="end_date" required
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', $term->end_date->toDateString()) }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               id="is_active" @checked(old('is_active', $term->is_active))>
                        <label class="form-check-label" for="is_active">
                            Active term
                        </label>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>
            </form>
        </div>
    </div>
</x-app-layout>