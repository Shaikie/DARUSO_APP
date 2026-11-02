<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Applies per-user notification channel preferences.
 *
 * In-app delivery is always persisted so the notification history stays
 * complete; the preference controls whether an additional email is sent.
 * Absence of a row means the category default applies, so new categories need
 * no data migration.
 */
class NotificationPreferenceService
{
    /**
     * Categories exposed on the preferences screen.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'general' => 'General announcements',
            'complaints' => 'Complaint updates',
            'meetings' => 'Meetings and events',
            'community' => 'Community activity',
            'leadership' => 'Leadership operations',
            'payments' => 'Payment requests and reminders',
        ];
    }

    /**
     * Resolve the effective preference for one category.
     *
     * @return array{in_app: bool, email: bool}
     */
    public function for(User $user, string $category = 'general'): array
    {
        $category = $this->normaliseCategory($category);

        $preference = NotificationPreference::query()
            ->where('user_id', $user->getKey())
            ->where('category', $category)
            ->first();

        return [
            'in_app' => $preference?->in_app ?? true,
            'email' => $preference?->email ?? false,
        ];
    }

    /**
     * Resolve one category for a collection of recipients without N+1 queries.
     *
     * @param  iterable<User>  $users
     * @return Collection<int, array{in_app: bool, email: bool}>
     */
    public function forMany(iterable $users, string $category = 'general'): Collection
    {
        $category = $this->normaliseCategory($category);
        $userIds = collect($users)->map(fn (User $user): int => $user->getKey())->values();
        $stored = NotificationPreference::query()
            ->whereIn('user_id', $userIds)
            ->where('category', $category)
            ->get()
            ->keyBy('user_id');

        return $userIds->mapWithKeys(fn (int $userId): array => [
            $userId => [
                'in_app' => (bool) ($stored[$userId]->in_app ?? true),
                'email' => (bool) ($stored[$userId]->email ?? false),
            ],
        ]);
    }

    /**
     * Every category with its effective preference, for the settings form.
     *
     * @return array<int, array{category: string, label: string, in_app: bool, email: bool}>
     */
    public function matrix(User $user): array
    {
        $stored = NotificationPreference::query()
            ->where('user_id', $user->getKey())
            ->get()
            ->keyBy('category');

        return collect(self::categories())
            ->map(fn (string $label, string $category): array => [
                'category' => $category,
                'label' => $label,
                'in_app' => (bool) ($stored[$category]->in_app ?? true),
                'email' => (bool) ($stored[$category]->email ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * Persist a submitted preference matrix.
     *
     * Only known categories are written, and the request is treated as a full
     * replacement of the stored matrix for the user.
     *
     * @param  array<string, array{in_app?: mixed, email?: mixed}>  $submitted
     */
    public function update(User $user, array $submitted): void
    {
        foreach (array_keys(self::categories()) as $category) {
            $values = $submitted[$category] ?? [];

            NotificationPreference::updateOrCreate(
                ['user_id' => $user->getKey(), 'category' => $category],
                [
                    'in_app' => filter_var($values['in_app'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'email' => filter_var($values['email'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
            );
        }
    }

    /**
     * Whether an in-app notification should be persisted for this recipient.
     */
    public function allowsInApp(User $user, string $category = 'general'): bool
    {
        return $this->for($user, $category)['in_app'];
    }

    /**
     * Whether an email copy should be attempted for this recipient.
     */
    public function allowsEmail(User $user, string $category = 'general'): bool
    {
        return $this->for($user, $category)['email'];
    }

    /**
     * Infer the category of a notification from the related model.
     */
    public function categoryFor(?Model $related): string
    {
        return match (true) {
            $related instanceof \App\Models\Complaint => 'complaints',
            $related instanceof \App\Models\Meeting,
            $related instanceof \App\Models\Event,
            $related instanceof \App\Models\CalendarEntry => 'meetings',
            $related instanceof \App\Models\Post,
            $related instanceof \App\Models\PostComment,
            $related instanceof \App\Models\Feedback => 'community',
            $related instanceof \App\Models\LeaderTask,
            $related instanceof \App\Models\Handover,
            $related instanceof \App\Models\LeadershipInvitation,
            $related instanceof \App\Models\MinistryActivityReport => 'leadership',
            $related instanceof \App\Models\PaymentRequest => 'payments',
            default => 'general',
        };
    }

    /**
     * @return array<int, string>
     */
    public function categoriesForUser(User $user): Collection
    {
        return collect(self::categories())->keys();
    }

    private function normaliseCategory(string $category): string
    {
        $category = Str::lower(trim($category));

        return array_key_exists($category, self::categories()) ? $category : 'general';
    }
}
