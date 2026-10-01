<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommitteeMemberRequest;
use App\Http\Requests\StoreCommitteeRequest;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\LeadershipTerm;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Committee::class);

        $committees = Committee::query()
            ->withCount('members')
            ->search($request->input('search'), ['name', 'description'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('leader.committees.index', ['committees' => $committees]);
    }

    public function create(): View
    {
        $this->authorize('create', Committee::class);

        return view('leader.committees.create');
    }

    public function store(StoreCommitteeRequest $request): RedirectResponse
    {
        $this->authorize('create', Committee::class);

        $committee = Committee::create($request->validated());

        $this->audit->log('created', $committee, null, ['name' => $committee->name], $request);

        return redirect()
            ->route('leader.committees.show', $committee)
            ->with('success', 'Committee created.');
    }

    public function show(Committee $committee): View
    {
        $this->authorize('view', $committee);

        return view('leader.committees.show', [
            'committee' => $committee,
            'members' => $committee->members()
                ->with(['user', 'term'])
                ->latest()
                ->get(),
            'terms' => LeadershipTerm::orderByDesc('start_date')->get(),
            'candidates' => User::query()
                ->orderBy('name')
                ->limit(500)
                ->get(),
            'defaultTermId' => LeadershipTerm::where('is_active', true)->value('id'),
        ]);
    }

    public function edit(Committee $committee): View
    {
        $this->authorize('update', $committee);

        return view('leader.committees.edit', ['committee' => $committee]);
    }

    public function update(StoreCommitteeRequest $request, Committee $committee): RedirectResponse
    {
        $this->authorize('update', $committee);

        $old = $committee->only(['name', 'description']);

        $committee->update($request->validated());

        $this->audit->log('updated', $committee, $old, $committee->only(['name', 'description']), $request);

        return redirect()
            ->route('leader.committees.show', $committee)
            ->with('success', 'Committee updated.');
    }

    /**
     * Add a member to the committee.
     */
    public function addMember(StoreCommitteeMemberRequest $request, Committee $committee): RedirectResponse
    {
        $this->authorize('manageMembers', $committee);

        $member = $committee->members()->create($request->validated());

        $this->audit->log('created', $member, null, [
            'committee' => $committee->name,
            'user_id' => $member->user_id,
        ], $request);

        return back()->with('success', 'Committee member added.');
    }

    public function removeMember(Request $request, Committee $committee, CommitteeMember $member): RedirectResponse
    {
        $this->authorize('manageMembers', $committee);

        if ($member->committee_id !== $committee->getKey()) {
            abort(404);
        }

        $this->audit->log('deleted', $member, ['committee' => $committee->name], null, $request);

        $member->delete();

        return back()->with('success', 'Committee member removed.');
    }

    public function destroy(Request $request, Committee $committee): RedirectResponse
    {
        $this->authorize('delete', $committee);

        $this->audit->log('deleted', $committee, $committee->only(['name']), null, $request);

        $committee->delete();

        return redirect()
            ->route('leader.committees.index')
            ->with('success', 'Committee deleted.');
    }
}
