<x-app-layout>
    @section('title', 'Documents')
    @section('heading', 'Documents')
    @section('subheading', 'Publications available to you.')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Title or description">
                </div>
                <div class="col-6 col-md-3">
                    <label for="category" class="form-label small fw-semibold">Category</label>
                    <select id="category" name="category" class="form-select form-select-sm">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->value }}" @selected(request('category') === $category->value)>
                                {{ $category->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['search', 'category']))
                        <a href="{{ route('student.documents.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="file-earmark-text" title="Available documents">
        @if ($documents->isEmpty())
            <x-empty-state icon="file-earmark-text"
                           title="No documents available"
                           description="Published documents and student policies will appear here." />
        @else
            <div class="row g-3">
                @foreach ($documents as $document)
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <a href="{{ route('student.documents.show', $document) }}"
                                   class="fw-semibold text-decoration-none">
                                    {{ $document->title }}
                                </a>
                                <span class="badge {{ $document->visibility->badgeClass() }}">
                                    {{ $document->visibility->label() }}
                                </span>
                            </div>

                            <p class="small text-muted mb-2">
                                {{ \Illuminate\Support\Str::limit($document->description, 110) }}
                            </p>

                            <div class="small text-muted mt-auto">
                                {{ $document->category->label() }}
                                · {{ $document->humanReadableSize() }}
                                @if ($document->version > 1)
                                    · v{{ $document->version }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($documents->hasPages())
                <div class="mt-3">{{ $documents->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>