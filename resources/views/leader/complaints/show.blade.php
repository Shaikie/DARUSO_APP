<x-app-layout>
    @section('title', $complaint->title)
    @section('heading', $complaint->title)
    @section('subheading', 'Complaint #'.$complaint->id)

    @section('actions')
        <a href="{{ route('leader.complaints.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>

        @can('delete', $complaint)
            <form method="POST" action="{{ route('leader.complaints.destroy', $complaint) }}"
                  onsubmit="return confirm('Delete this complaint and its history?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="chat-left-text" title="Complaint">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <x-status-badge :status="$complaint->status" />
                    <span class="badge text-bg-light border">
                        {{ \App\Enums\ComplaintCategory::from($complaint->category)->label() }}
                    </span>
                </div>

                <div class="communication-body">{{ $complaint->description }}</div>

                @if ($complaint->attachments->isNotEmpty())
                    <hr>
                    <h3 class="h6 fw-semibold">Attachments</h3>
                    <ul class="list-unstyled mb-0">
                        @foreach ($complaint->attachments as $attachment)
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

            {{--
                Workflow actions. Both forms post to the same controller action; the
                policy decides which abilities the current leader holds.
            --}}
            @canany(['transition', 'assign', 'forward'], $complaint)
                <x-page-card class="mt-4" icon="arrow-repeat" title="Workflow actions">
                    @can('transition', $complaint)
                        <form method="POST" action="{{ route('leader.complaints.update', $complaint) }}" class="mb-4">
                            @csrf
                            @method('PUT')

                            <h3 class="h6 fw-semibold mb-2">Update status</h3>

                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label for="status" class="form-label small fw-semibold">New status</label>
                                    <select id="status" name="status" required
                                            class="form-select form-select-sm @error('status') is-invalid @enderror">
                                        <option value="">Choose…</option>
                                        @foreach ($allowedTransitions as $transition)
                                            <option value="{{ $transition->value }}"
                                                @selected(old('status') === $transition->value)>
                                                {{ $transition->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        Only valid next steps are listed. Resolving requires
                                        <code>complaint.resolve</code>.
                                    </div>
                                </div>

                                <div class="col-12 col-md-8">
                                    <label for="notes" class="form-label small fw-semibold">Notes for the history log</label>
                                    <textarea id="notes" name="notes" rows="2"
                                              class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                              placeholder="What changed and why">{{ old('notes') }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <button class="btn btn-primary btn-sm mt-3">
                                <i class="bi bi-check-lg me-1"></i>Apply status change
                            </button>
                        </form>
                    @endcan

                    @can('assign', $complaint)
                        <hr>
                        <h3 class="h6 fw-semibold mb-2">Assign</h3>

                        <form method="POST" action="{{ route('leader.complaints.update', $complaint) }}">
                            @csrf
                            @method('PUT')
                            {{-- Status is unchanged by an assignment-only submission. --}}
                            <input type="hidden" name="status" value="{{ $complaint->status->value }}">

                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label for="assigned_ministry_id" class="form-label small fw-semibold">Ministry</label>
                                    <select id="assigned_ministry_id" name="assigned_ministry_id"
                                            class="form-select form-select-sm @error('assigned_ministry_id') is-invalid @enderror">
                                        <option value="">Unchanged</option>
                                        @foreach ($ministries as $ministry)
                                            <option value="{{ $ministry->id }}"
                                                @selected(old('assigned_ministry_id', $complaint->assigned_ministry_id) == $ministry->id)>
                                                {{ $ministry->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_ministry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="assigned_leader_id" class="form-label small fw-semibold">Leader</label>
                                    <select id="assigned_leader_id" name="assigned_leader_id"
                                            class="form-select form-select-sm @error('assigned_leader_id') is-invalid @enderror">
                                        <option value="">Unchanged</option>
                                        @foreach ($leaders as $leader)
                                            <option value="{{ $leader->id }}"
                                                @selected(old('assigned_leader_id', $complaint->assigned_leader_id) == $leader->id)>
                                                {{ $leader->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_leader_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="assign-notes" class="form-label small fw-semibold">Notes</label>
                                    <input type="text" id="assign-notes" name="notes"
                                           class="form-control form-control-sm"
                                           placeholder="Handing over to the welfare ministry">
                                </div>
                            </div>

                            <button class="btn btn-outline-primary btn-sm mt-3">
                                <i class="bi bi-person-check me-1"></i>Save assignment
                            </button>
                        </form>
                    @endcan

                    @can('forward', $complaint)
                        <hr>
                        <h3 class="h6 fw-semibold mb-2">Forward to a ministry</h3>

                        <form method="POST" action="{{ route('leader.complaints.forward', $complaint) }}">
                            @csrf
                            <div class="row g-2">
                                <div class="col-12 col-md-5">
                                    <label for="forward_ministry_id" class="form-label small fw-semibold">Ministry</label>
                                    <select id="forward_ministry_id" name="ministry_id" required
                                            class="form-select form-select-sm @error('ministry_id') is-invalid @enderror">
                                        <option value="">Choose…</option>
                                        @foreach ($ministries as $ministry)
                                            <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('ministry_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="forward_leader_id" class="form-label small fw-semibold">Leader (optional)</label>
                                    <select id="forward_leader_id" name="leader_id"
                                            class="form-select form-select-sm @error('leader_id') is-invalid @enderror">
                                        <option value="">Any leader</option>
                                        @foreach ($leaders as $leader)
                                            <option value="{{ $leader->id }}">{{ $leader->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label for="forward_notes" class="form-label small fw-semibold">Reason</label>
                                    <input type="text" id="forward_notes" name="notes"
                                           class="form-control form-select-sm" placeholder="Reason for forwarding">
                                </div>
                            </div>

                            <button class="btn btn-outline-secondary btn-sm mt-3">
                                <i class="bi bi-send me-1"></i>Forward complaint
                            </button>
                        </form>
                    @endcan
                </x-page-card>
            @endcanany

            <x-page-card class="mt-4" icon="clock-history" title="History">
                @if ($complaint->history->isEmpty())
                    <p class="text-muted small mb-0">No history entries recorded yet.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($complaint->history as $entry)
                            <li class="d-flex gap-3 pb-3 border-bottom">
                                <div class="flex-shrink-0 text-muted small text-nowrap" style="width: 8rem;">
                                    {{ $entry->created_at?->format('d M Y H:i') }}
                                </div>
                                <div>
                                    <div class="fw-semibold small text-capitalize">
                                        {{ str_replace('_', ' ', $entry->action) }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $entry->old_status?->label() ?? '—' }}
                                        <i class="bi bi-arrow-right mx-1"></i>
                                        {{ $entry->new_status?->label() ?? '—' }}
                                    </div>
                                    @if ($entry->notes)
                                        <div class="small mt-1">{{ $entry->notes }}</div>
                                    @endif
                                    <div class="small text-muted mt-1">
                                        by {{ $entry->actor?->name ?? 'System' }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="person" title="Submitted by">
                <div class="fw-semibold">{{ $complaint->creator?->name ?? 'Unknown' }}</div>
                @if ($complaint->creator?->studentProfile)
                    <dl class="row mb-0 mt-2 small">
                        <dt class="col-5 text-muted fw-normal">Programme</dt>
                        <dd class="col-7">{{ $complaint->creator->studentProfile->programme }}</dd>
                        <dt class="col-5 text-muted fw-normal">Year</dt>
                        <dd class="col-7">{{ $complaint->creator->studentProfile->year_of_study }}</dd>
                    </dl>
                @endif
                <div class="small text-muted mt-2">
                    Submitted {{ $complaint->created_at->format('d M Y H:i') }}
                </div>
            </x-page-card>

            <x-page-card class="mt-4" icon="person-check" title="Handling">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Ministry</dt>
                    <dd class="col-7">{{ $complaint->assignedMinistry?->name ?? 'Unassigned' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Leader</dt>
                    <dd class="col-7">{{ $complaint->assignedLeader?->name ?? 'Unassigned' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Resolved at</dt>
                    <dd class="col-7">{{ $complaint->resolved_at?->format('d M Y H:i') ?? '—' }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>