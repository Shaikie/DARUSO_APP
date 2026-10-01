<x-app-layout>
    @section('title', 'New complaint')
    @section('heading', 'Submit a complaint')
    @section('subheading', 'Describe the issue and leadership will track it for you.')

    @section('actions')
        <a href="{{ route('student.complaints.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('student.complaints.store') }}" enctype="multipart/form-data">
                @csrf

                <x-page-card icon="pencil-square" title="Complaint details">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}"
                               placeholder="A short summary of the issue">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-semibold">Category</label>
                        <select id="category" name="category" required
                                class="form-select @error('category') is-invalid @enderror">
                            <option value="">Choose a category…</option>
                            @foreach (\App\Enums\ComplaintCategory::cases() as $category)
                                <option value="{{ $category->value }}" @selected(old('category') === $category->value)>
                                    {{ $category->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">This helps route your complaint to the right ministry.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="8" required minlength="20"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Describe what happened, where, and when.">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label fw-semibold">Attachment (optional)</label>
                        <input type="file" id="attachment" name="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Max {{ config('daruso.uploads.max_size_kb') }} KB. Only you and authorised
                            leaders handling this complaint can read the file.
                        </div>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Submit complaint
                    </button>
                </x-page-card>
            </form>
        </div>

        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <x-page-card icon="shield-lock" title="Your privacy">
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-2">Your complaint is private. Only you and the leaders authorised to handle it can read it.</li>
                    <li class="mb-2">You will be notified each time the status changes.</li>
                    <li>You can add more information while the complaint is open.</li>
                </ul>
            </x-page-card>
        </div>
    </div>
</x-app-layout>