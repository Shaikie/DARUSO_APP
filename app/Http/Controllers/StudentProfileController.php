<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Enums\StudentStatus;
use App\Http\Requests\StoreStudentProfileRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Onboarding for a newly registered account.
 *
 * A registrant has an account but no student record, so the student dashboard and
 * complaint portal are unavailable until this form is submitted. Registration
 * numbers are unique, which enforces "one record per student" at the schema
 * level; any mismatch is corrected later by an administrator.
 */
class StudentProfileController extends Controller
{
    public function store(StoreStudentProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Guard against a second submission creating a duplicate record.
        if ($user->studentProfile()->exists()) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Your student record already exists.');
        }

        DB::transaction(function () use ($user, $request): void {
            $user->studentProfile()->create([
                ...$request->academicPayload(),
                // Self-submitted academic details must be reviewed by an
                // authorised leader before the student is marked active.
                'status' => StudentStatus::PendingVerification->value,
            ]);

            $studentRole = Role::firstOrCreate(
                ['name' => RoleName::Student->value],
                ['description' => RoleName::Student->label()],
            );

            $user->roles()->syncWithoutDetaching([$studentRole->getKey()]);
        });

        return redirect()->route('student.dashboard')
            ->with('success', 'Your student record has been created.');
    }
}
