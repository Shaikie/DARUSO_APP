<?php

namespace App\Services;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\AudienceRule;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Drives the announcement lifecycle.
 *
 * Lifecycle: draft → review → published → expired/archived. Keeping transitions
 * in one place means publication, approval and archiving each record the right
 * audit event and cannot be bypassed from a controller.
 */
class AnnouncementPublisher
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Submit a draft for review.
     */
    public function submitForReview(Announcement $announcement, User $actor, ?Request $request = null): Announcement
    {
        $old = $announcement->status->value;

        $announcement->update(['status' => AnnouncementStatus::Review]);

        $this->audit->log('submitted', $announcement, ['status' => $old], ['status' => AnnouncementStatus::Review->value], $request);

        return $announcement;
    }

    /**
     * Publish an announcement.
     *
     * Items requiring approval record the approver, which is intentionally not
     * the author: the permission to approve is separate from authoring.
     *
     * @param  Collection<int, AudienceRule>|null  $rules  Replacement rules when publishing
     */
    public function publish(
        Announcement $announcement,
        User $actor,
        ?iterable $rules = null,
        ?Request $request = null,
    ): Announcement {
        $old = [
            'status' => $announcement->status->value,
            'published_at' => $announcement->published_at?->toDateTimeString(),
        ];

        if ($announcement->requires_approval) {
            $announcement->approved_by = $actor->getKey();
        }

        $announcement->update([
            'status' => AnnouncementStatus::Published,
            'published_at' => now(),
        ]);

        if ($rules !== null) {
            $announcement->audienceRules()->sync(collect($rules)->pluck('id')->all());
        }

        $this->audit->log('published', $announcement, $old, [
            'status' => AnnouncementStatus::Published->value,
        ], $request);

        return $announcement;
    }

    /**
     * Archive a published announcement, removing it from reader listings.
     */
    public function archive(Announcement $announcement, User $actor, ?Request $request = null): Announcement
    {
        $old = $announcement->status->value;

        $announcement->update(['status' => AnnouncementStatus::Archived]);

        $this->audit->log('archived', $announcement, ['status' => $old], ['status' => AnnouncementStatus::Archived->value], $request);

        return $announcement;
    }

    /**
     * Mark a published announcement expired (typically after its expiry date).
     */
    public function expire(Announcement $announcement, User $actor, ?Request $request = null): Announcement
    {
        $old = $announcement->status->value;

        $announcement->update(['status' => AnnouncementStatus::Expired]);

        $this->audit->log('expired', $announcement, ['status' => $old], ['status' => AnnouncementStatus::Expired->value], $request);

        return $announcement;
    }

    /**
     * Return an announcement to draft so it can be revised.
     */
    public function revertToDraft(Announcement $announcement, User $actor, ?Request $request = null): Announcement
    {
        $old = $announcement->status->value;

        $announcement->update([
            'status' => AnnouncementStatus::Draft,
            'approved_by' => null,
        ]);

        $this->audit->log('updated', $announcement, ['status' => $old], ['status' => AnnouncementStatus::Draft->value], $request);

        return $announcement;
    }
}
