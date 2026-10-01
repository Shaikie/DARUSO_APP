<x-app-layout>
    @section('title', 'New ministry')
    @section('heading', 'New ministry')
    @section('subheading', 'Define a ministry portfolio.')

    @section('actions')
        <a href="{{ route('leader.ministries.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.ministries.store') }}">
                @csrf

                <x-page-card icon="building" title="Ministry details">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Academic Ministry">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="What this ministry is responsible for.">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Create ministry
                    </button>
                </x-page-card>
            </form>
        </div>
    </div>
</x-app-layout>