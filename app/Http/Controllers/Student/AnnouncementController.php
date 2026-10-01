<?php

namespace App\Http\Controllers\Student;

use App\Enums\Priority;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AudienceResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Announcements as seen by a reader.
 *
 * Both listing and detail go through the audience engine, so an announcement
 * addressed to another group is simply not retrievable.
 */
class AnnouncementController extends Controller
{
    public function __construct(
        private readonly AudienceResolver $audience,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Visible if the audience covers this student, or if they authored it.
        $announcements = Announcement::query()
            ->with(['author', 'audienceRules'])
            ->visibleToAudience()
            ->where(function (Builder $query) use ($user): void {
                $this->audience->constrainSubjectForUser($query, Announcement::class, $user);
                $query->orWhere('author_id', $user->getKey());
            })
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->search($request->input('search'), ['title', 'content'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('student.announcements.index', [
            'announcements' => $announcements,
            'priorities' => Priority::cases(),
        ]);
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $user = $request->user();

        $this->authorize('view', $announcement);

        // Defence in depth: the policy checks audience membership, and this
        // scope guarantees the record is both published and unexpired.
        abort_unless(
            $this->audience->constrainSubjectForUser(
                Announcement::query()->visibleToAudience(),
                Announcement::class,
                $user,
            )->whereKey($announcement->getKey())->exists(),
            404
        );

        return view('student.announcements.show', [
            'announcement' => $announcement->load(['author', 'attachments']),
        ]);
    }
}
