<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMinistryRequest;
use App\Models\Ministry;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MinistryController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ministry::class);

        $ministries = Ministry::query()
            ->withCount(['assignments', 'complaints'])
            ->search($request->input('search'), ['name', 'description'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('leader.ministries.index', ['ministries' => $ministries]);
    }

    public function create(): View
    {
        $this->authorize('create', Ministry::class);

        return view('leader.ministries.create');
    }

    public function store(StoreMinistryRequest $request): RedirectResponse
    {
        $this->authorize('create', Ministry::class);

        $ministry = Ministry::create($request->validated());

        $this->audit->log('created', $ministry, null, ['name' => $ministry->name], $request);

        return redirect()
            ->route('leader.ministries.show', $ministry)
            ->with('success', 'Ministry created.');
    }

    public function show(Ministry $ministry): View
    {
        $this->authorize('view', $ministry);

        return view('leader.ministries.show', [
            'ministry' => $ministry,
            'assignments' => $ministry->assignments()
                ->with(['user', 'position', 'term'])
                ->latest()
                ->get(),
            'openComplaints' => $ministry->complaints()->open()->count(),
        ]);
    }

    public function edit(Ministry $ministry): View
    {
        $this->authorize('update', $ministry);

        return view('leader.ministries.edit', ['ministry' => $ministry]);
    }

    public function update(StoreMinistryRequest $request, Ministry $ministry): RedirectResponse
    {
        $this->authorize('update', $ministry);

        $old = $ministry->only(['name', 'description']);

        $ministry->update($request->validated());

        $this->audit->log('updated', $ministry, $old, $ministry->only(['name', 'description']), $request);

        return redirect()
            ->route('leader.ministries.show', $ministry)
            ->with('success', 'Ministry updated.');
    }

    public function destroy(Request $request, Ministry $ministry): RedirectResponse
    {
        $this->authorize('delete', $ministry);

        $this->audit->log('deleted', $ministry, $ministry->only(['name']), null, $request);

        $ministry->delete();

        return redirect()
            ->route('leader.ministries.index')
            ->with('success', 'Ministry deleted.');
    }
}
