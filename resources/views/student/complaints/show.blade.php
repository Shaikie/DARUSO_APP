<x-app-layout>
    @section('title', $complaint->title)
    @section('heading', $complaint->title)
    @section('subheading', 'Complaint #'.$complaint->id)

    @section('actions')
        <a href="{{ route('student.complaints.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>

        @can('update', $complaint)
            <a href="{{ route('student.complaints.edit', $complaint) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Add information
            </a>
        @endcan

        @can('delete', $complaint)
            <form method="POST" action="{{ route('student.complaints.destroy', $complaint) }}"
                  onsubmit="return confirm('Withdraw this complaint?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Withdraw
                </button>
            </form>
        @endcan
    @endsection

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <x-page-card icon="chat-left-text" title="Your complaint">
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

            <x-page-card class="mt-4" icon="clock-history" title="Progress">
                @if ($complaint->history->isEmpty())
                    <p class="text-muted small mb-0">No updates yet.</p>
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
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-4">
            <x-page-card icon="person-check" title="Who is handling it">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Ministry</dt>
                    <dd class="col-7">{{ $complaint->assignedMinistry?->name ?? 'Awaiting assignment' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Leader</dt>
                    <dd class="col-7">{{ $complaint->assignedLeader?->name ?? 'Awaiting assignment' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Submitted</dt>
                    <dd class="col-7">{{ $complaint->created_at->format('d M Y') }}</dd>

                    <dt class="col-5 text-muted fw-normal">Last update</dt>
                    <dd class="col-7">{{ $complaint->updated_at->format('d M Y') }}</dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>