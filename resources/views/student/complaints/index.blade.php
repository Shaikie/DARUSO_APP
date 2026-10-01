<x-app-layout>
    @section('title', 'My complaints')
    @section('heading', 'My complaints')
    @section('subheading', 'Track the status of the complaints you have submitted.')

    @section('actions')
        @can('create', App\Models\Complaint::class)
            <a href="{{ route('student.complaints.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New complaint
            </a>
        @endcan
    @endsection

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="folder-open" label="Total" :value="$complaints->total()" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="hourglass-split" label="Open" :value="$openCount" tone="warning" />
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-4">
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
                <div class="col-6 col-md-4">
                    <button class="btn btn-sm btn-outline-primary" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if (request()->filled('status'))
                        <a href="{{ route('student.complaints.index') }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-page-card icon="exclamation-triangle" title="Complaints">
        @if ($complaints->isEmpty())
            <x-empty-state icon="chat-left-text"
                           title="You have not submitted any complaints"
                           description="Use the complaint portal to raise an issue with DARUSO leadership.">
                @can('create', App\Models\Complaint::class)
                    <x-slot:action>
                        <a href="{{ route('student.complaints.create') }}" class="btn btn-sm btn-primary">
                            Submit a complaint
                        </a>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Complaint</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col">Handled by</th>
                            <th scope="col">Submitted</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $complaint)
                            <tr>
                                <td class="fw-semibold">{{ $complaint->title }}</td>
                                <td class="small">{{ \App\Enums\ComplaintCategory::from($complaint->category)->label() }}</td>
                                <td><x-status-badge :status="$complaint->status" /></td>
                                <td class="small">
                                    {{ $complaint->assignedMinistry?->name ?? 'Awaiting assignment' }}
                                </td>
                                <td class="small text-nowrap">{{ $complaint->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('student.complaints.show', $complaint) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Track
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