<x-app-layout>
    @section('title', 'Complaints')
    @section('heading', 'Complaints')
    @section('subheading', 'Complaints within your remit, with workflow actions.')

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="inbox" label="Open" :value="$stats['open']" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="person-exclamation" label="Unassigned" :value="$stats['unassigned']" tone="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="check-circle" label="Resolved" :value="$stats['resolved']" tone="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="list-check" label="Total (all)" :value="$stats['open'] + $stats['resolved']" tone="secondary" />
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Title or description">
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
                    <label for="category" class="form-label small fw-semibold">Category</label>
                    <select id="category" name="category" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach (\App\Enums\ComplaintCategory::cases() as $category)
                            <option value="{{ $category->value }}" @selected(request('category') === $category->value)>
                                {{ $category->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['search', 'status', 'category']))
                        <a href="{{ route('leader.complaints.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="exclamation-triangle" title="Complaints">
        @if ($complaints->isEmpty())
            <x-empty-state icon="inbox"
                           title="No complaints in your remit"
                           description="Complaints assigned to your ministry, assigned to you personally, or not yet assigned appear here." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Complaint</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col">Assigned to</th>
                            <th scope="col">Student</th>
                            <th scope="col">Submitted</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $complaint)
                            <tr>
                                <td>
                                    <a href="{{ route('leader.complaints.show', $complaint) }}"
                                       class="fw-semibold text-decoration-none">
                                        {{ $complaint->title }}
                                    </a>
                                    <div class="small text-muted text-truncate" style="max-width: 24rem;">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($complaint->description), 90) }}
                                    </div>
                                </td>
                                <td class="small">{{ \App\Enums\ComplaintCategory::from($complaint->category)->label() }}</td>
                                <td><x-status-badge :status="$complaint->status" /></td>
                                <td class="small">
                                    @if ($complaint->assignedMinistry)
                                        <div>{{ $complaint->assignedMinistry->name }}</div>
                                    @endif
                                    <div class="text-muted">{{ $complaint->assignedLeader?->name ?? 'No leader assigned' }}</div>
                                </td>
                                <td class="small">{{ $complaint->creator?->name ?? '—' }}</td>
                                <td class="small text-nowrap">{{ $complaint->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('leader.complaints.show', $complaint) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>Handle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($complaints->hasPages())
                <div class="mt-3">{{ $complaints->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>