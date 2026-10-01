<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaderAssignmentRequest;
use App\Models\LeaderAssignment;
use App\Models\LeadershipTerm;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Leadership assignments within leadership terms.
 */
class LeadershipController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaderAssignment::class);

        $term = $request->filled('term')
            ? LeadershipTerm::find($request->integer('term'))
            : LeadershipTerm::where('is_active', true)->first() ?? LeadershipTerm::orderByDesc('start_date')->first();

        $assignments = LeaderAssignment::query()
            ->with(['user', 'position', 'ministry', 'term'])
            ->when($term !== null, fn ($query) => $query->where('leadership_term_id', $term->getKey()))
            ->when($request->filled('ministry'), fn ($query) => $query->where('ministry_id', $request->integer('ministry')))
            ->when($request->filled('position'), fn ($query) => $query->where('position_id', $request->integer('position')))
            ->join('positions', 'positions.id', '=', 'leader_assignments.position_id')
            ->orderBy('positions.hierarchy_level')
            ->select('leader_assignments.*')
            ->paginate(20)
            ->withQueryString();

        return view('leader.leadership.index', [
            'assignments' => $assignments,
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
            'selectedTerm' => $term,
            'ministries' => Ministry::orderBy('name')->get(),
            'positions' => Position::orderBy('hierarchy_level')->get(),
            'candidates' => User::query()->orderBy('name')->limit(500)->get(),
            'defaultTermId' => $term?->getKey(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LeaderAssignment::class);

        return view('leader.leadership.create', [
            'positions' => Position::orderBy('hierarchy_level')->get(),
            'ministries' => Ministry::orderBy('name')->get(),
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
            'selectedTerm' => $request->integer('term') ?: LeadershipTerm::where('is_active', true)->value('id'),
            'candidates' => User::query()->orderBy('name')->limit(500)->get(),
        ]);
    }

    public function store(StoreLeaderAssignmentRequest $request): RedirectResponse
    {
        $this->authorize('create', LeaderAssignment::class);

        $assignment = LeaderAssignment::create($request->safe()->only([
            'user_id', 'position_id', 'ministry_id', 'leadership_term_id',
        ]));

        // Anyone holding a position is, by definition, a leader.
        $assignment->user()->first()?->leaderProfile()->firstOrCreate([]);

        $this->audit->log('assigned', $assignment, null, [
            'user_id' => $assignment->user_id,
            'position_id' => $assignment->position_id,
        ], $request);

        return redirect()
            ->route('leader.leadership.index')
            ->with('success', 'Leadership assignment created.');
    }

    public function show(LeaderAssignment $leaderAssignment): View
    {
        $this->authorize('view', $leaderAssignment);

        return view('leader.leadership.show', [
            'assignment' => $leaderAssignment->load(['user', 'position', 'ministry', 'term']),
        ]);
    }

    public function edit(LeaderAssignment $leaderAssignment): View
    {
        $this->authorize('update', $leaderAssignment);

        return view('leader.leadership.edit', [
            'assignment' => $leaderAssignment,
            'positions' => Position::orderBy('hierarchy_level')->get(),
            'ministries' => Ministry::orderBy('name')->get(),
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
        ]);
    }

    public function update(StoreLeaderAssignmentRequest $request, LeaderAssignment $leaderAssignment): RedirectResponse
    {
        $this->authorize('update', $leaderAssignment);

        $old = $leaderAssignment->only(['user_id', 'position_id', 'ministry_id', 'leadership_term_id']);

        $leaderAssignment->update($request->safe()->only([
            'user_id', 'position_id', 'ministry_id', 'leadership_term_id',
        ]));

        $this->audit->log('updated', $leaderAssignment, $old, $leaderAssignment->only([
            'user_id', 'position_id', 'ministry_id', 'leadership_term_id',
        ]), $request);

        return redirect()
            ->route('leader.leadership.index')
            ->with('success', 'Leadership assignment updated.');
    }

    public function destroy(Request $request, LeaderAssignment $leaderAssignment): RedirectResponse
    {
        $this->authorize('delete', $leaderAssignment);

        $this->audit->log('deleted', $leaderAssignment, $leaderAssignment->only([
            'user_id', 'position_id', 'leadership_term_id',
        ]), null, $request);

        $leaderAssignment->delete();

        return redirect()
            ->route('leader.leadership.index')
            ->with('success', 'Leadership assignment removed.');
    }
}
