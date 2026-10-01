<x-app-layout>
    @section('title', 'Announcements')
    @section('heading', 'Announcements')
    @section('subheading', 'Create, target, review and publish announcements.')

    @section('actions')
        @can('create', App\Models\Announcement::class)
            <a href="{{ route('leader.announcements.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New announcement
            </a>
        @endcan
    @endsection

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Title or content">
                </div>
                <div class="col-6 col-md-3">
                    <label for="status" class="form-label small fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button class="btn btn-sm btn-outline-primary w-100" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
                @if (request()->hasAny(['search', 'status']))
                    <div class="col-6 col-md-2">
                        <a href="{{ route('leader.announcements.index') }}" class="btn btn-sm btn-link w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <x-page-card icon="megaphone" title="All announcements">
        @if ($announcements->isEmpty())
            <x-empty-state icon="megaphone"
                           title="No announcements found"
                           description="Adjust the filters, or create the first announcement.">
                @can('create', App\Models\Announcement::class)
                    <x-slot:action>
                        <a href="{{ route('leader.announcements.create') }}" class="btn btn-sm btn-primary">
                            New announcement
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
                            <th scope="col">Priority</th>
                            <th scope="col">Status</th>
                            <th scope="col">Audience</th>
                            <th scope="col">Author</th>
                            <th scope="col">Published</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($announcements as $announcement)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.announcements.show', $announcement) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $announcement->title }}
                                    </a>
                                    @if ($announcement->requires_approval)
                                        <span class="badge text-bg-light border ms-1">Approval required</span>
                                    @endif
                                    <div class="small text-muted text-truncate" style="max-width: 22rem;">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 90) }}
                                    </div>
                                </td>
                                <td><x-priority-badge :priority="$announcement->priority" /></td>
                                <td><x-status-badge :status="$announcement->status" /></td>
                                <td class="small">
                                    @forelse ($announcement->audienceRules as $rule)
                                        <span class="badge text-bg-light border me-1">
                                            {{ $rule->type()->label() }}{{ $rule->audience_value ? ': '.$rule->audience_value : '' }}
                                        </span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td class="small">{{ $announcement->author?->name ?? '—' }}</td>
                                <td class="small text-nowrap">
                                    {{ $announcement->published_at?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('leader.announcements.show', $announcement) }}"
                                           class="btn btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('update', $announcement)
                                            <a href="{{ route('leader.announcements.edit', $announcement) }}"
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('publish', $announcement)
                                            @if (! $announcement->isPublished())
                                                <form method="POST"
                                                      action="{{ route('leader.announcements.publish', $announcement) }}">
                                                    @csrf
                                                    <button class="btn btn-outline-success" title="Publish">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('delete', $announcement)
                                            <form method="POST"
                                                  action="{{ route('leader.announcements.destroy', $announcement) }}"
                                                  onsubmit="return confirm('Delete this announcement?');">
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

            @if ($announcements->hasPages())
                <div class="mt-3">{{ $announcements->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>