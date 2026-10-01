<x-app-layout>
    @section('title', $document->title)
    @section('heading', $document->title)
    @section('subheading', $document->category->label())

    @section('actions')
        <a href="{{ route('student.documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('student.documents.download', $document) }}" class="btn btn-primary">
            <i class="bi bi-download me-1"></i>Download
        </a>
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="file-earmark-text" title="About this document">
                <p class="communication-body">{{ $document->description ?: 'No description provided.' }}</p>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="Details">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Visibility</dt>
                    <dd class="col-7">{{ $document->visibility->label() }}</dd>

                    <dt class="col-5 text-muted fw-normal">Version</dt>
                    <dd class="col-7">{{ $document->version }}</dd>

                    <dt class="col-5 text-muted fw-normal">File</dt>
                    <dd class="col-7">{{ $document->file_name }}</dd>

                    <dt class="col-5 text-muted fw-normal">Size</dt>
                    <dd class="col-7">{{ $document->humanReadableSize() }}</dd>

                    <dt class="col-5 text-muted fw-normal">Uploaded</dt>
                    <dd class="col-7">
                        {{ $document->uploader?->name ?? 'DARUSO' }}<br>
                        <span class="text-muted">{{ $document->created_at->format('d M Y') }}</span>
                    </dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>