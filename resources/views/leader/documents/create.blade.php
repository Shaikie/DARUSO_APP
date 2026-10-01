<x-app-layout>
    @section('title', 'Upload document')
    @section('heading', 'Upload document')
    @section('subheading', 'Files are stored privately and served through an authorized download.')

    @section('actions')
        <a href="{{ route('leader.documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('leader.documents.store') }}" enctype="multipart/form-data">
                @csrf

                <x-page-card icon="upload" title="Document details">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="e.g. DARUSO Constitution">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="category" class="form-label fw-semibold">Category</label>
                            <select id="category" name="category" required
                                    class="form-select @error('category') is-invalid @enderror">
                                <option value="">Choose a category…</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->value }}" @selected(old('category') === $category->value)>
                                        {{ $category->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="visibility" class="form-label fw-semibold">Visibility</label>
                            <select id="visibility" name="visibility" required
                                    class="form-select @error('visibility') is-invalid @enderror">
                                <option value="">Choose visibility…</option>
                                @foreach ($visibilities as $visibility)
                                    <option value="{{ $visibility->value }}"
                                        @selected(old('visibility', 'students') === $visibility->value)>
                                        {{ $visibility->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('visibility') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="ministry_id" class="form-label fw-semibold">Ministry</label>
                            <select id="ministry_id" name="ministry_id"
                                    class="form-select @error('ministry_id') is-invalid @enderror">
                                <option value="">Not ministry-specific</option>
                                @foreach ($ministries as $ministry)
                                    <option value="{{ $ministry->id }}" @selected(old('ministry_id') == $ministry->id)>
                                        {{ $ministry->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ministry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="committee_id" class="form-label fw-semibold">Committee</label>
                            <select id="committee_id" name="committee_id"
                                    class="form-select @error('committee_id') is-invalid @enderror">
                                <option value="">Not committee-specific</option>
                                @foreach ($committees as $committee)
                                    <option value="{{ $committee->id }}" @selected(old('committee_id') == $committee->id)>
                                        {{ $committee->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('committee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold">File</label>
                        <input type="file" id="file" name="file" required
                               class="form-control @error('file') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Up to {{ config('daruso.uploads.max_size_kb') }} KB. Allowed:
                            {{ implode(', ', config('daruso.uploads.mimes')) }}.
                            Executable and script files are rejected on upload.
                        </div>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Upload document
                    </button>
                </x-page-card>
            </form>
        </div>

        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <x-page-card icon="shield-lock" title="How access works">
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-2">Files are stored on a private disk, never a public path.</li>
                    <li class="mb-2">
                        <strong>Ministry</strong> and <strong>Committee</strong> visibility additionally
                        require membership of that specific ministry or committee.
                    </li>
                    <li class="mb-2">Every download re-checks the viewer's permission.</li>
                    <li>Uploading a new file creates a new version rather than replacing the old one.</li>
                </ul>
            </x-page-card>
        </div>
    </div>
</x-app-layout>