<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Records sensitive actions in the audit trail.
 *
 * Writes are deliberately decoupled from the caller's transaction: an audit
 * failure must never roll back the business action that was successfully
 * performed, so problems are swallowed and logged rather than propagated.
 */
class AuditLogger
{
    public function log(
        string|AuditAction $action,
        ?Model $target = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): void {
        try {
            AuditLog::create([
                'actor_id' => Auth::id(),
                'action' => $action instanceof AuditAction ? $action->value : $action,
                'target_type' => $target?->getMorphClass(),
                'target_id' => $target?->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Log a state transition using the target's attributes as the payload.
     */
    public function logChange(
        string|AuditAction $action,
        Model $target,
        array $oldValues,
        array $newValues,
        ?Request $request = null,
    ): void {
        $this->log($action, $target, $oldValues, $newValues, $request);
    }
}
