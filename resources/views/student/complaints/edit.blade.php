<x-app-layout>
    @section('title', 'Add information')
    @section('heading', 'Add information')
    @section('subheading', $complaint->title)

    @section('actions')
        <a href="{{ route('student.complaints.show', $complaint) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row">
        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('student.complaints.update', $complaint) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-page-card icon="pencil-square" title="Complaint details">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" id="title" name="title" required minlength="5" maxlength="255"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $complaint->title) }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-semibold">Category</label>
                        <select id="category" name="category" required
                                class="form-select @error('category') is-invalid @enderror">
                            @foreach (\App\Enums\ComplaintCategory::cases() as $category)
                                <option value="{{ $category->value }}"
                                    @selected(old('category', $complaint->category) === $category->value)>
                                    {{ $category->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="8" required minlength="20"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $complaint->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label fw-semibold">Add attachment</label>
                        <input type="file" id="attachment" name="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.jpg,.jpeg,.png,.gif,.webp,.zip">
                        @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save changes
                    </button>
                </x-page-card>
            </form>
        </div>

        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
            <x-page-card icon="info-circle" title="Note">
                <p class="small text-muted mb-0">
                    Editing here adds information to your complaint. Status changes are made by
                    DARUSO leaders, and you are notified when that happens.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>