<?php

namespace App\Http\Controllers\Student;

use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use App\Services\AttachmentService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Student complaint submission and tracking.
 *
 * Complaints are private to their creator. Every action is authorized against
 * ComplaintPolicy, which checks ownership, so changing an id in the URL returns
 * 403 rather than another student's complaint.
 */
class ComplaintController extends Controller
{
    public function __construct(
        private readonly AttachmentService $attachments,
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $complaints = $user->complaints()
            ->with(['assignedMinistry', 'assignedLeader'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('student.complaints.index', [
            'complaints' => $complaints,
            'statuses' => ComplaintStatus::cases(),
            'openCount' => $user->complaints()->open()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Complaint::class);

        return view('student.complaints.create');
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $this->authorize('create', Complaint::class);

        $complaint = Complaint::create([
            ...$request->safe()->only(['title', 'description', 'category']),
            'creator_id' => $request->user()->getKey(),
            'status' => ComplaintStatus::Submitted->value,
        ]);

        if ($request->hasFile('attachment')) {
            $this->attachments->storeFor($complaint, $request->file('attachment'), $request->user()->getKey());
        }

        // The opening history entry records the submission itself.
        $complaint->history()->create([
            'actor_id' => $request->user()->getKey(),
            'action' => 'submitted',
            'old_status' => null,
            'new_status' => ComplaintStatus::Submitted->value,
            'notes' => 'Complaint submitted by the student.',
        ]);

        return redirect()
            ->route('student.complaints.show', $complaint)
            ->with('success', 'Your complaint has been submitted.');
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);

        return view('student.complaints.show', [
            'complaint' => $complaint->load([
                'assignedMinistry', 'assignedLeader', 'history.actor', 'attachments',
            ]),
        ]);
    }

    public function edit(Complaint $complaint): View
    {
        $this->authorize('update', $complaint);

        return view('student.complaints.edit', ['complaint' => $complaint]);
    }

    /**
     * Students may add context to their own complaint, but cannot change status.
     */
    public function update(StoreComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('update', $complaint);

        $complaint->update($request->safe()->only(['title', 'description', 'category']));

        if ($request->hasFile('attachment')) {
            $this->attachments->storeFor($complaint, $request->file('attachment'), $request->user()->getKey());
        }

        return redirect()
            ->route('student.complaints.show', $complaint)
            ->with('success', 'Complaint updated.');
    }

    /**
     * Students withdraw their own complaint while it is still open.
     */
    public function destroy(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('delete', $complaint);

        $complaint->delete();

        return redirect()
            ->route('student.complaints.index')
            ->with('success', 'Complaint withdrawn.');
    }
}
