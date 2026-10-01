<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadershipTermRequest;
use App\Models\LeadershipTerm;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Leadership terms.
 *
 * Exactly one term may be active, so activation runs in a transaction that
 * deactivates the previous term first. Terms are never deleted while active.
 */
class LeadershipTermController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeadershipTerm::class);

        $terms = LeadershipTerm::query()
            ->withCount(['assignments', 'committeeMembers'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('leader.leadership-terms.index', ['terms' => $terms]);
    }

    public function create(): View
    {
        $this->authorize('create', LeadershipTerm::class);

        return view('leader.leadership-terms.create');
    }

    public function store(StoreLeadershipTermRequest $request): RedirectResponse
    {
        $this->authorize('create', LeadershipTerm::class);

        $wantsActive = $request->boolean('is_active');

        $term = DB::transaction(function () use ($request, $wantsActive): LeadershipTerm {
            if ($wantsActive) {
                LeadershipTerm::query()->update(['is_active' => false]);
            }

            return LeadershipTerm::create([
                'name' => $request->string('name')->value,
                'start_date' => $request->date('start_date'),
                'end_date' => $request->date('end_date'),
                'is_active' => $wantsActive,
            ]);
        });

        $this->audit->log('created', $term, null, [
            'name' => $term->name,
            'is_active' => $term->is_active,
        ], $request);

        return redirect()
            ->route('leader.leadership-terms.index')
            ->with('success', 'Leadership term created.');
    }

    public function show(LeadershipTerm $leadershipTerm): View
    {
        $this->authorize('view', $leadershipTerm);

        return view('leader.leadership-terms.show', [
            'term' => $leadershipTerm,
            'assignments' => $leadershipTerm->assignments()
                ->with(['user', 'position', 'ministry'])
                ->get(),
            'members' => $leadershipTerm->committeeMembers()
                ->with(['user', 'committee'])
                ->get(),
        ]);
    }

    public function edit(LeadershipTerm $leadershipTerm): View
    {
        $this->authorize('update', $leadershipTerm);

        return view('leader.leadership-terms.edit', ['term' => $leadershipTerm]);
    }

    public function update(StoreLeadershipTermRequest $request, LeadershipTerm $leadershipTerm): RedirectResponse
    {
        $this->authorize('update', $leadershipTerm);

        $old = $leadershipTerm->only(['name', 'start_date', 'end_date', 'is_active']);
        $wantsActive = $request->boolean('is_active');

        DB::transaction(function () use ($request, $leadershipTerm, $wantsActive): void {
            if ($wantsActive && ! $leadershipTerm->is_active) {
                LeadershipTerm::query()
                    ->whereKeyNot($leadershipTerm->getKey())
                    ->update(['is_active' => false]);
            }

            $leadershipTerm->update([
                'name' => $request->string('name')->value,
                'start_date' => $request->date('start_date'),
                'end_date' => $request->date('end_date'),
                'is_active' => $wantsActive,
            ]);
        });

        $this->audit->log('updated', $leadershipTerm, $old, $leadershipTerm->only([
            'name', 'start_date', 'end_date', 'is_active',
        ]), $request);

        return redirect()
            ->route('leader.leadership-terms.index')
            ->with('success', 'Leadership term updated.');
    }

    /**
     * Activate a term, deactivating whichever term was active before.
     */
    public function activate(Request $request, LeadershipTerm $leadershipTerm): RedirectResponse
    {
        $this->authorize('activate', $leadershipTerm);

        DB::transaction(function () use ($leadershipTerm): void {
            LeadershipTerm::query()
                ->whereKeyNot($leadershipTerm->getKey())
                ->update(['is_active' => false]);

            $leadershipTerm->update(['is_active' => true]);
        });

        $this->audit->log('updated', $leadershipTerm, ['is_active' => false], ['is_active' => true], $request);

        return back()->with('success', "{$leadershipTerm->name} is now the active term.");
    }

    public function destroy(Request $request, LeadershipTerm $leadershipTerm): RedirectResponse
    {
        $this->authorize('delete', $leadershipTerm);

        $this->audit->log('deleted', $leadershipTerm, $leadershipTerm->only(['name']), null, $request);

        $leadershipTerm->delete();

        return redirect()
            ->route('leader.leadership-terms.index')
            ->with('success', 'Leadership term deleted.');
    }
}
