<x-app-layout>
    @section('title', $announcement->title)
    @section('heading', $announcement->title)
    @section('subheading', 'Announcement from '.($announcement->author?->name ?? 'DARUSO leadership'))

    @section('actions')
        <a href="{{ route('student.announcements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="megaphone" title="Announcement">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <x-priority-badge :priority="$announcement->priority" />
                    @if ($announcement->expires_at)
                        <span class="badge text-bg-light border">
                            Valid until {{ $announcement->expires_at->format('d M Y') }}
                        </span>
                    @endif
                </div>

                <div class="communication-body fs-6">{{ $announcement->content }}</div>

                @if ($announcement->attachments->isNotEmpty())
                    <hr>
                    <h2 class="h6 fw-semibold">Attachments</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach ($announcement->attachments as $attachment)
                            <li class="mb-1">
                                <a href="{{ route('attachments.download', $attachment) }}" class="text-decoration-none">
                                    <i class="bi bi-paperclip me-1"></i>{{ $attachment->file_name }}
                                </a>
                                <span class="text-muted small">({{ $attachment->humanReadableSize() }})</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="info-circle" title="Details">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Published by</dt>
                    <dd class="col-7">{{ $announcement->author?->name ?? 'DARUSO leadership' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Published</dt>
                    <dd class="col-7">{{ $announcement->published_at?->format('d M Y H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Priority</dt>
                    <dd class="col-7">{{ $announcement->priority?->label() }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>