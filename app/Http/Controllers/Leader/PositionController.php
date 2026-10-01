<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Models\Position;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Position::class);

        $positions = Position::query()
            ->withCount('assignments')
            ->search($request->input('search'), ['name'])
            ->orderBy('hierarchy_level')
            ->paginate(20)
            ->withQueryString();

        return view('leader.positions.index', ['positions' => $positions]);
    }

    public function create(): View
    {
        $this->authorize('create', Position::class);

        return view('leader.positions.create');
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $this->authorize('create', Position::class);

        $position = Position::create($request->validated());

        $this->audit->log('created', $position, null, ['name' => $position->name], $request);

        return redirect()
            ->route('leader.positions.index')
            ->with('success', 'Position created.');
    }

    public function show(Position $position): View
    {
        $this->authorize('view', $position);

        return view('leader.positions.show', [
            'position' => $position,
            'assignments' => $position->assignments()
                ->with(['user', 'ministry', 'term'])
                ->latest()
                ->get(),
        ]);
    }

    public function edit(Position $position): View
    {
        $this->authorize('update', $position);

        return view('leader.positions.edit', ['position' => $position]);
    }

    public function update(StorePositionRequest $request, Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $old = $position->only(['name', 'hierarchy_level', 'description']);

        $position->update($request->validated());

        $this->audit->log('updated', $position, $old, $position->only(['name', 'hierarchy_level']), $request);

        return redirect()
            ->route('leader.positions.index')
            ->with('success', 'Position updated.');
    }

    public function destroy(Request $request, Position $position): RedirectResponse
    {
        $this->authorize('delete', $position);

        $this->audit->log('deleted', $position, $position->only(['name']), null, $request);

        $position->delete();

        return redirect()
            ->route('leader.positions.index')
            ->with('success', 'Position deleted.');
    }
}
