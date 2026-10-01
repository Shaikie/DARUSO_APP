<x-app-layout>
    @section('title', 'New position')
    @section('heading', 'New position')
    @section('subheading', 'Positions describe leadership roles in the structure.')

    @section('actions')
        <a href="{{ route('leader.positions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.positions.store') }}">
                @csrf

                <x-page-card icon="diagram-3" title="Position details">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Secretary General">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="hierarchy_level" class="form-label fw-semibold">Hierarchy level</label>
                        <input type="number" id="hierarchy_level" name="hierarchy_level" required
                               min="1" max="100"
                               class="form-control @error('hierarchy_level') is-invalid @enderror"
                               value="{{ old('hierarchy_level', 1) }}">
                        @error('hierarchy_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Lower numbers rank higher in the leadership structure.</div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Create position
                    </button>
                </x-page-card>
            </form>
        </div>
    </div>
</x-app-layout>