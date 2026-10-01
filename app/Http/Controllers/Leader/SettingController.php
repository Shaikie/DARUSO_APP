<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSystemSettingsRequest;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * System settings.
 */
class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly AuditLogger $audit,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('leader.settings.index', [
            'settings' => Setting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
            'values' => $this->settings->all(),
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $old = $this->settings->all();
        $payload = $request->settingsPayload();

        // Checkbox values are absent when unticked; record an explicit false.
        foreach (['allow_public_announcements', 'allow_student_registration', 'notification_email_enabled', 'notification_sms_enabled'] as $flag) {
            if (! array_key_exists($flag, $payload)) {
                $payload[$flag] = false;
            }
        }

        $this->settings->put($payload);

        $this->audit->log('settings_updated', null, $old, $payload, $request);

        return back()->with('success', 'Settings updated.');
    }
}
