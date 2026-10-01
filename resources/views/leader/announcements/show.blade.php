<x-app-layout>
    @section('title', $announcement->title)
    @section('heading', $announcement->title)
    @section('subheading', 'Announcement details and lifecycle')

    @section('actions')
        <a href="{{ route('leader.announcements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>

        @can('update', $announcement)
            <a href="{{ route('leader.announcements.edit', $announcement) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        @endcan

        @can('submitForReview', $announcement)
            <form method="POST" action="{{ route('leader.announcements.submit', $announcement) }}">
                @csrf
                <button class="btn btn-outline-warning">
                    <i class="bi bi-send me-1"></i>Submit for review
                </button>
            </form>
        @endcan

        @can('publish', $announcement)
            @unless ($announcement->isPublished())
                <form method="POST" action="{{ route('leader.announcements.publish', $announcement) }}">
                    @csrf
                    <button class="btn btn-success">
                        <i class="bi bi-broadcast me-1"></i>Publish
                    </button>
                </form>
            @endunless

            @if ($announcement->isPublished())
                <form method="POST" action="{{ route('leader.announcements.archive', $announcement) }}">
                    @csrf
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-archive me-1"></i>Archive
                    </button>
                </form>
            @endif
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="megaphone" title="Content">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <x-status-badge :status="$announcement->status" />
                    <x-priority-badge :priority="$announcement->priority" />
                    @if ($announcement->hasExpired())
                        <span class="badge text-bg-dark">Past expiry date</span>
                    @endif
                </div>

                <div class="communication-body fs-6">{{ $announcement->content }}</div>

                @if ($announcement->attachments->isNotEmpty())
                    <hr>
                    <h3 class="h6 fw-semibold">Attachments</h3>
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
            <x-page-card icon="info-circle" title="Metadata">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Author</dt>
                    <dd class="col-7">{{ $announcement->author?->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Created</dt>
                    <dd class="col-7">{{ $announcement->created_at->format('d M Y H:i') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Published</dt>
                    <dd class="col-7">{{ $announcement->published_at?->format('d M Y H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Expires</dt>
                    <dd class="col-7">{{ $announcement->expires_at?->format('d M Y H:i') ?? 'No expiry' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Approval</dt>
                    <dd class="col-7">
                        {{ $announcement->requires_approval ? 'Required' : 'Not required' }}
                        @if ($announcement->approved_by)
                            <br><span class="text-muted">by {{ $announcement->approver?->name }}</span>
                        @endif
                    </dd>
                </dl>
            </x-page-card>

            <x-page-card class="mt-4" icon="bullseye" title="Target audience">
                @forelse ($audienceSummary as $line)
                    <div class="mb-1"><i class="bi bi-dot me-1"></i>{{ $line }}</div>
                @empty
                    <p class="text-muted small mb-0">No audience rules are attached to this announcement.</p>
                @endforelse

                <p class="text-muted small mt-3 mb-0">
                    Rules are stored, not recipient rows, so this announcement remains a single
                    record regardless of how many students the audience covers.
                </p>
            </x-page-card>
        </div>
    </div>
</x-app-layout>