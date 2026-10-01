<x-app-layout>
    @section('title', $document->title)
    @section('heading', $document->title)
    @section('subheading', $document->category->label().' · version '.$document->version)

    @section('actions')
        <a href="{{ route('leader.documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('leader.documents.download', $document) }}" class="btn btn-primary">
            <i class="bi bi-download me-1"></i>Download
        </a>
        @can('update', $document)
            <a href="{{ route('leader.documents.edit', $document) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="file-earmark-text" title="About">
                <span class="badge {{ $document->visibility->badgeClass() }} mb-3">
                    {{ $document->visibility->label() }}
                </span>

                <div class="communication-body">
                    {{ $document->description ?: 'No description provided.' }}
                </div>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="File details">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Filename</dt>
                    <dd class="col-7">{{ $document->file_name }}</dd>

                    <dt class="col-5 text-muted fw-normal">Type</dt>
                    <dd class="col-7">{{ $document->file_mime_type ?? 'Unknown' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Size</dt>
                    <dd class="col-7">{{ $document->humanReadableSize() }}</dd>

                    <dt class="col-5 text-muted fw-normal">Version</dt>
                    <dd class="col-7">{{ $document->version }}</dd>

                    @if ($document->supersedes)
                        <dt class="col-5 text-muted fw-normal">Supersedes</dt>
                        <dd class="col-7">
                            <a href="{{ route('leader.documents.show', $document->supersedes) }}">
                                v{{ $document->supersedes->version }}
                            </a>
                        </dd>
                    @endif

                    <dt class="col-5 text-muted fw-normal">Uploaded by</dt>
                    <dd class="col-7">
                        {{ $document->uploader?->name ?? '—' }}<br>
                        <span class="text-muted">{{ $document->created_at->format('d M Y') }}</span>
                    </dd>
                </dl>
            </x-page-card>

            @if ($document->ministry || $document->committee)
                <x-page-card class="mt-4" icon="lock" title="Scope">
                    <dl class="row mb-0 small">
                        @if ($document->ministry)
                            <dt class="col-5 text-muted fw-normal">Ministry</dt>
                            <dd class="col-7">{{ $document->ministry->name }}</dd>
                        @endif
                        @if ($document->committee)
                            <dt class="col-5 text-muted fw-normal">Committee</dt>
                            <dd class="col-7">{{ $document->committee->name }}</dd>
                        @endif
                    </dl>
                    <p class="small text-muted mt-2 mb-0">
                        Only members of this group can read the file.
                    </p>
                </x-page-card>
            @endif
        </div>
    </div>
</x-app-layout>