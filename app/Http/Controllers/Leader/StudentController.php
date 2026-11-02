<?php

namespace App\Http\Controllers\Leader;

use App\Enums\AccountStatus;
use App\Enums\StudentStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentProfileRequest;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\StudentVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Student administration.
 *
 * Field-level sensitivity is applied through StudentProfilePolicy, so this
 * controller never decides what a leader may see — the view asks the policy per
 * field group.
 */
class StudentController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly StudentVerificationService $verification,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentProfile::class);

        $students = StudentProfile::query()
            ->with('user')
            ->search(
                $request->input('search'),
                // Registration numbers and email addresses are sensitive, so they
                // are only searchable when the actor may view sensitive data.
                $request->user()->can('viewSensitive', new StudentProfile)
                    ? ['registration_number', 'programme']
                    : ['programme'],
            )
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $term = mb_strtolower(trim((string) $request->input('search')));

                $query->orWhereHas('user', function (Builder $userQuery) use ($term): void {
                    $userQuery->whereRaw('LOWER(users.name) LIKE ?', ["%{$term}%"]);

                    if ($request->user()->can('viewSensitive', new StudentProfile)) {
                        $userQuery->orWhereRaw('LOWER(users.email) LIKE ?', ["%{$term}%"]);
                    }
                });
            })
            ->when($request->filled('college'), fn ($query) => $query->where('college', $request->string('college')))
            ->when($request->filled('programme'), fn ($query) => $query->where('programme', $request->string('programme')))
            ->when($request->filled('year_of_study'), fn ($query) => $query->where('year_of_study', $request->integer('year_of_study')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('leader.students.index', [
            'students' => $students,
            'statuses' => StudentStatus::cases(),
            'filters' => [
                'colleges' => StudentProfile::distinct()->orderBy('college')->pluck('college'),
                'programmes' => StudentProfile::distinct()->orderBy('programme')->pluck('programme'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StudentProfile::class);

        return view('leader.students.create');
    }

    public function store(StoreStudentProfileRequest $request): RedirectResponse
    {
        $this->authorize('create', StudentProfile::class);

        [$user, $profile] = DB::transaction(function () use ($request): array {
            $user = User::create([
                ...$request->accountPayload(),
                // No shared/default password. The student receives a one-time
                // password-reset link and must establish their own credential.
                'password' => Str::random(64),
                'account_status' => AccountStatus::Pending,
            ]);

            $profile = $user->studentProfile()->create([
                ...$request->academicPayload(),
                'status' => StudentStatus::PendingVerification->value,
                'verification_status' => VerificationStatus::Pending->value,
                'submitted_at' => now(),
            ]);

            return [$user, $profile];
        });

        event(new Registered($user));
        Password::sendResetLink(['email' => $user->email]);

        $this->audit->log('created', $profile, null, [
            'registration_number' => $profile->registration_number,
        ], $request);

        return redirect()
            ->route('leader.students.show', $profile)
            ->with('success', 'Student record created.');
    }

    public function show(StudentProfile $student): View
    {
        $this->authorize('view', $student);

        return view('leader.students.show', [
            'student' => $student->load('user'),
            'complaints' => $student->user->complaints()
                ->with('assignedMinistry')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function edit(StudentProfile $student): View
    {
        $this->authorize('update', $student);

        return view('leader.students.edit', [
            'student' => $student->load('user'),
            'statuses' => StudentStatus::cases(),
        ]);
    }

    public function update(StoreStudentProfileRequest $request, StudentProfile $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $old = $student->only(['registration_number', 'programme', 'year_of_study', 'status']);
        $previousStatus = $student->status;
        $requestedStatus = $request->filled('status') && $request->user()->can('viewSensitive', $student)
            ? StudentStatus::from($request->string('status')->value())
            : null;

        DB::transaction(function () use ($request, $student, $requestedStatus): void {
            $student->update($request->academicPayload());

            if ($requestedStatus !== null && $requestedStatus !== StudentStatus::Active) {
                $student->update(['status' => $requestedStatus]);

                if ($requestedStatus === StudentStatus::PendingVerification) {
                    $student->update([
                        'verification_status' => VerificationStatus::Pending,
                        'submitted_at' => now(),
                        'verified_at' => null,
                        'verified_by' => null,
                    ]);
                }
            }

            if ($request->user()->can('update', $student->user)) {
                $student->user->update($request->accountPayload());
            }
        });

        $student->refresh();

        if ($requestedStatus === StudentStatus::Active
            && ($previousStatus !== StudentStatus::Active || ! $student->isVerified())) {
            $student = $this->verification->verify($student, $request->user(), null, $request);
        }

        $this->audit->log('updated', $student, $old, $student->only(['registration_number', 'programme', 'year_of_study', 'status']), $request);

        return redirect()
            ->route('leader.students.show', $student)
            ->with('success', 'Student record updated.');
    }

    public function destroy(Request $request, StudentProfile $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $this->audit->log('deleted', $student, $student->only(['registration_number']), null, $request);

        DB::transaction(fn () => $student->user->delete());

        return redirect()
            ->route('leader.students.index')
            ->with('success', 'Student record deleted.');
    }
}
