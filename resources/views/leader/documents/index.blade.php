<x-app-layout>
    @section('title', 'Documents')
    @section('heading', 'Documents')
    @section('subheading', 'Published documents and their audience.')

    @section('actions')
        @can('create', App\Models\Document::class)
            <a href="{{ route('leader.documents.create') }}" class="btn btn-primary">
                <i class="bi bi-upload me-1"></i>Upload document
            </a>
        @endcan
    @endsection

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
                        <a href="{{ route('leader.documents.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="file-earmark-text" title="Documents">
        @if ($documents->isEmpty())
            <x-empty-state icon="file-earmark-text"
                           title="No documents"
                           description="Upload a document and choose who may read it.">
                @can('create', App\Models\Document::class)
                    <x-slot:action>
                        <a href="{{ route('leader.documents.create') }}" class="btn btn-sm btn-primary">
                            Upload a document
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Category</th>
                            <th scope="col">Visibility</th>
                            <th scope="col">Version</th>
                            <th scope="col">Uploaded</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.documents.show', $document) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $document->title }}
                                    </a>
                                    <div class="small text-muted">{{ $document->file_name }}</div>
                                </td>
                                <td class="small">{{ $document->category->label() }}</td>
                                <td>
                                    <span class="badge {{ $document->visibility->badgeClass() }}">
                                        {{ $document->visibility->label() }}
                                    </span>
                                </td>
                                <td class="small">v{{ $document->version }}</td>
                                <td class="small text-nowrap">
                                    {{ $document->uploader?->name ?? '—' }}<br>
                                    <span class="text-muted">{{ $document->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('leader.documents.download', $document) }}"
                                           class="btn btn-outline-secondary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="{{ route('leader.documents.show', $document) }}"
                                           class="btn btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $document)
                                            <a href="{{ route('leader.documents.edit', $document) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $document)
                                            <form method="POST"
                                                  action="{{ route('leader.documents.destroy', $document) }}"
                                                  onsubmit="return confirm('Delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($documents->hasPages())
                <div class="mt-3">{{ $documents->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>