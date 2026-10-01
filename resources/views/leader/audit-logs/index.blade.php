<x-app-layout>
    @section('title', 'Audit log')
    @section('heading', 'Audit log')
    @section('subheading', 'Append-only record of sensitive actions.')

    <div class="alert alert-info d-flex gap-2" role="alert">
        <i class="bi bi-info-circle-fill mt-1"></i>
        <div class="small">
            Audit entries are written automatically and cannot be edited or deleted —
            there is no route that mutates a log, and the model refuses those operations.
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="search" class="form-label small fw-semibold">Search</label>
                    <input type="search" id="search" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="Action or target type">
                </div>
                <div class="col-6 col-md-3">
                    <label for="action" class="form-label small fw-semibold">Action</label>
                    <select id="action" name="action" class="form-select form-select-sm">
                        <option value="">All actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="target_type" class="form-label small fw-semibold">Target type</label>
                    <select id="target_type" name="target_type" class="form-select form-select-sm">
                        <option value="">All targets</option>
                        @foreach ($targetTypes as $type)
                            <option value="{{ $type }}" @selected(request('target_type') === $type)>
                                {{ class_basename($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->hasAny(['search', 'action', 'target_type']))
                        <a href="{{ route('leader.audit-logs.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="journal-text" title="Recorded activity">
        @if ($logs->isEmpty())
            <x-empty-state icon="journal-text"
                           title="No audit entries match"
                           description="Sensitive actions such as role changes, publication and complaint updates are recorded here." />
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Actor</th>
                            <th scope="col">Action</th>
                            <th scope="col">Target</th>
                            <th scope="col">Changes</th>
                            <th scope="col">Origin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="small text-nowrap">
                                    {{ $log->created_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="small">{{ $log->actor?->name ?? 'System' }}</td>
                                <td>
                                    <span class="badge text-bg-light border text-capitalize">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td class="small">
                                    @if ($log->target_type)
                                        {{ class_basename($log->target_type) }} #{{ $log->target_id }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($log->old_values || $log->new_values)
                                        <details>
                                            <summary class="text-decoration-none">View</summary>
                                            <div class="mt-1">
                                                @if ($log->old_values)
                                                    <div class="text-danger">
                                                        <i class="bi bi-dash"></i>
                                                        {{ collect($log->old_values)->map(fn ($v, $k) => "$k: $v")->implode(', ') }}
                                                    </div>
                                                @endif
                                                @if ($log->new_values)
                                                    <div class="text-success">
                                                        <i class="bi bi-plus"></i>
                                                        {{ collect($log->new_values)->map(fn ($v, $k) => "$k: $v")->implode(', ') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="mt-3">{{ $logs->links() }}</div>
            @endif
        @endif
    </x-page-card>
</x-app-layout>