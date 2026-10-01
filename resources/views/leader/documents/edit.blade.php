<x-app-layout>
    @section('title', 'Edit document')
    @section('heading', 'Edit document')
    @section('subheading', $document->title)

    @section('actions')
        <a href="{{ route('leader.documents.show', $document) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.documents.update', $document) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-page-card icon="pencil-square" title="Document details">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $document->title) }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $document->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="category" class="form-label fw-semibold">Category</label>
                            <select id="category" name="category" required
                                    class="form-select @error('category') is-invalid @enderror">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->value }}"
                                        @selected(old('category', $document->category?->value) === $category->value)>
                                        {{ $category->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="visibility" class="form-label fw-semibold">Visibility</label>
                            <select id="visibility" name="visibility" required
                                    class="form-select @error('visibility') is-invalid @enderror">
                                @foreach ($visibilities as $visibility)
                                    <option value="{{ $visibility->value }}"
                                        @selected(old('visibility', $document->visibility?->value) === $visibility->value)>
                                        {{ $visibility->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('visibility') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="ministry_id" class="form-label fw-semibold">Ministry</label>
                            <select id="ministry_id" name="ministry_id"
                                    class="form-select @error('ministry_id') is-invalid @enderror">
                                <option value="">Not ministry-specific</option>
                                @foreach ($ministries as $ministry)
                                    <option value="{{ $ministry->id }}"
                                        @selected(old('ministry_id', $document->ministry_id) == $ministry->id)>
                                        {{ $ministry->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ministry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="committee_id" class="form-label fw-semibold">Committee</label>
                            <select id="committee_id" name="committee_id"
                                    class="form-select @error('committee_id') is-invalid @enderror">
                                <option value="">Not committee-specific</option>
                                @foreach ($committees as $committee)
                                    <option value="{{ $committee->id }}"
                                        @selected(old('committee_id', $document->committee_id) == $committee->id)>
                                        {{ $committee->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('committee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold">Replace file (optional)</label>
                        <input type="file" id="file" name="file"
                               class="form-control @error('file') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                        <div class="form-text">
                            Current file: {{ $document->file_name }} (version {{ $document->version }}).
                            Uploading a new file creates version {{ $document->version + 1 }} and keeps this
                            version on record.
                        </div>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>
            </form>
        </div>
    </div>
</x-app-layout>