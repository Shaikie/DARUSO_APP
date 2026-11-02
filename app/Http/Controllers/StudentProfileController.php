<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentProfileRequest;
use App\Models\User;
use App\Services\StudentVerificationService;
use Illuminate\Http\RedirectResponse;

class StudentProfileController extends Controller
{
    public function __construct(
        private readonly StudentVerificationService $verification,
    ) {}

    public function store(StoreStudentProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->studentProfile()->exists()) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Your student record already exists.');
        }

        $this->verification->submit($user, $request->academicPayload(), $request);

        return redirect()->route('student.dashboard')
            ->with('success', 'Your student record has been submitted for verification.');
    }
}
