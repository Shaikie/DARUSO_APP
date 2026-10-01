<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only audit log browser.
 *
 * There is deliberately no create, update or delete action: the trail is
 * append-only, and AuditLog itself refuses those operations.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::query()
            ->with('actor')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('actor_id'), fn ($query) => $query->where('actor_id', $request->integer('actor_id')))
            ->when($request->filled('target_type'), fn ($query) => $query->where('target_type', $request->string('target_type')))
            ->search($request->input('search'), ['action', 'target_type'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('leader.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'targetTypes' => AuditLog::query()->whereNotNull('target_type')->distinct()->orderBy('target_type')->pluck('target_type'),
        ]);
    }
}
