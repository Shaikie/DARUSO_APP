<?php

namespace App\Http\Controllers\Leader;

use App\Enums\ComplaintStatus;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForwardComplaintRequest;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Models\Complaint;
use App\Models\Ministry;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ComplaintWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Complaint handling for leaders.
 *
 * Status changes go through ComplaintWorkflow, which validates transitions and
 * writes history. This controller only authorizes and delegates.
 */
class ComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintWorkflow $workflow,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Complaint::class);

        $complaints = Complaint::query()
            ->with(['creator', 'assignedMinistry', 'assignedLeader'])
            // Scoped to complaints this leader is permitted to act on, using the
            // same rule as ComplaintPolicy::view so the list and the detail page
            // can never disagree.
            ->where(function (Builder $query) use ($request): void {
                $user = $request->user();

                if ($user->hasPermission(PermissionName::ComplaintAssign->value)) {
                    // Organisation-wide remit: no scoping needed.
                    return;
                }

                $query->where('assigned_leader_id', $user->getKey())
                    ->orWhereIn('assigned_ministry_id', $user->ministryIds()->all());
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->search($request->input('search'), ['title', 'description'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leader.complaints.index', [
            'complaints' => $complaints,
            'statuses' => ComplaintStatus::cases(),
            'stats' => [
                'open' => Complaint::query()->open()->count(),
                'unassigned' => Complaint::query()->whereNull('assigned_ministry_id')->open()->count(),
                'resolved' => Complaint::query()->whereNotNull('resolved_at')->count(),
            ],
        ]);
    }

    /**
     * Leaders do not create complaints; this exists so the resource route is not
     * a dead link in navigation.
     */
    public function create(): View
    {
        abort(403, 'Complaints are submitted by students.');
    }

    public function store(Request $request): RedirectResponse
    {
        abort(403, 'Complaints are submitted by students.');
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);

        return view('leader.complaints.show', [
            'complaint' => $complaint->load([
                'creator.studentProfile',
                'assignedMinistry',
                'assignedLeader',
                'history.actor',
                'attachments',
            ]),
            'ministries' => Ministry::orderBy('name')->get(),
            'leaders' => User::query()
                ->whereHas('leaderProfile')
                ->orderBy('name')
                ->get(),
            'allowedTransitions' => $complaint->status->allowedTransitions(),
        ]);
    }

    public function edit(Request $request, Complaint $complaint): RedirectResponse
    {
        return redirect()->route('leader.complaints.show', $complaint);
    }

    /**
     * Change a complaint's status.
     */
    public function update(UpdateComplaintStatusRequest $request, Complaint $complaint): RedirectResponse
    {
        $target = ComplaintStatus::from($request->string('status')->value);

        // Resolving needs its own permission; other transitions need update.
        $ability = $target === ComplaintStatus::Resolved ? 'resolve' : 'transition';
        $this->authorize($ability, $complaint);

        $ministry = $request->filled('assigned_ministry_id')
            ? Ministry::find($request->integer('assigned_ministry_id'))
            : null;

        $leader = $request->filled('assigned_leader_id')
            ? User::find($request->integer('assigned_leader_id'))
            : null;

        if (($ministry !== null || $leader !== null) && ! $this->canAssign($request, $complaint)) {
            abort(403, 'You do not have permission to assign complaints.');
        }

        if ($ministry !== null || $leader !== null) {
            $this->workflow->assign($complaint, $request->user(), $ministry, $leader, $request->input('notes'));
        }

        try {
            $this->workflow->transitionTo(
                $complaint,
                $target,
                $request->user(),
                $request->input('notes'),
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('status_changed', $complaint, ['status' => 'previous'], ['status' => $target->value], $request);

        return back()->with('success', "Complaint status updated to {$target->label()}.");
    }

    /**
     * Forward a complaint to a ministry.
     */
    public function forward(ForwardComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('forward', $complaint);

        $ministry = Ministry::findOrFail($request->integer('ministry_id'));
        $leader = $request->filled('leader_id') ? User::find($request->integer('leader_id')) : null;

        $this->workflow->forward($complaint, $ministry, $request->user(), $request->input('notes'));

        $this->audit->log('forwarded', $complaint, null, [
            'ministry' => $ministry->name,
            'leader' => $leader?->name,
        ], $request);

        return back()->with('success', "Complaint forwarded to {$ministry->name}.");
    }

    public function destroy(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorize('delete', $complaint);

        $this->audit->log('deleted', $complaint, $complaint->only(['title', 'status']), null, $request);

        $complaint->delete();

        return redirect()
            ->route('leader.complaints.index')
            ->with('success', 'Complaint deleted.');
    }

    private function canAssign(Request $request, Complaint $complaint): bool
    {
        return $request->user()->can('assign', $complaint);
    }
}
