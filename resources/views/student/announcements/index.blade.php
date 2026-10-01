<x-app-layout>
    @section('title', 'Announcements')
    @section('heading', 'Announcements')
    @section('subheading', 'Notices published to you and your groups.')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Title or content">
                </div>
                <div class="col-6 col-md-3">
                    <label for="priority" class="form-label small fw-semibold">Priority</label>
                    <select id="priority" name="priority" class="form-select form-select-sm">
                        <option value="">All priorities</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['search', 'priority']))
                        <a href="{{ route('student.announcements.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="megaphone" title="Your announcements">
        @if ($announcements->isEmpty())
            <x-empty-state icon="megaphone"
                           title="No announcements yet"
                           description="Announcements addressed to your college, programme, hostel, year or to all students will appear here." />
        @else
            <div class="list-group list-group-flush">
                @foreach ($announcements as $announcement)
                    <div class="list-group-item px-0 py-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <a href="{{ route('student.announcements.show', $announcement) }}"
                               class="fw-semibold text-decoration-none">
                                {{ $announcement->title }}
                            </a>
                            <x-priority-badge :priority="$announcement->priority" />
                        </div>

                        <p class="mb-1 small text-muted">
                            {{ \Illuminate\Support\Str::limit($announcement->content, 220) }}
                        </p>

                        <div class="small text-muted">
                            {{ $announcement->author?->name ?? 'DARUSO leadership' }}
                            @if ($announcement->published_at)
                                · {{ $announcement->published_at->format('d M Y') }}
                            @endif
                            @if ($announcement->expires_at)
                                · expires {{ $announcement->expires_at->format('d M Y') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($announcements->hasPages())
                <div class="mt-3">{{ $announcements->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>