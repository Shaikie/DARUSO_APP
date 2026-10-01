<?php

namespace App\Services;

use App\Enums\AudienceType;
use App\Enums\RoleName;
use App\Models\Announcement;
use App\Models\AudienceRule;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\Event;
use App\Models\LeaderAssignment;
use App\Models\Meeting;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * Resolves audience rules into the set of users who should see a message.
 *
 * The design goal is that a university-wide announcement stays a single rule
 * row. Resolution therefore produces *queries*, not materialised recipient
 * lists, so "all students" costs one indexed lookup rather than 30,000 inserts.
 *
 * Two entry points matter:
 *
 *  - `constrainSubjectForUser()` filters a listing (announcements, meetings,
 *    events) to what a given reader may see, using a single correlated EXISTS.
 *  - `recipientIds()` materialises ids, and is only used for delivery where the
 *    audience is genuinely bounded.
 */
class AudienceResolver
{
    /**
     * Restrict a subject query (e.g. Announcement) to records addressed to a user.
     *
     * Implemented as one EXISTS over the pivot so a message matching several
     * rules is still returned once, and so no recipient rows are read.
     *
     * @template TSubject of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TSubject>  $query
     * @param  class-string<TSubject>  $subject
     * @param  string  $boolean  How to combine with any existing constraints.
     * @return Builder<TSubject>
     */
    public function constrainSubjectForUser(
        Builder $query,
        string $subject,
        User $user,
        string $boolean = 'and',
    ): Builder {
        return $query->whereExists(
            $this->audienceSubquery($query, $subject, $user),
            boolean: $boolean
        );
    }

    /**
     * Build the correlated EXISTS subquery that matches a reader's audience.
     *
     * Exposed separately so callers can combine it with other conditions — for
     * example "addressed to me OR organised by me" — inside a single `where`
     * group, rather than nesting two full queries.
     *
     * @template TSubject of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TSubject>  $query
     * @param  class-string<TSubject>  $subject
     * @return \Closure(QueryBuilder): void
     */
    public function audienceSubquery(Builder $query, string $subject, User $user): \Closure
    {
        [$ruleTable, $pivotTable, $pivotColumn] = $this->pivotFor($subject);

        $model = $query->getModel();
        $qualifiedKey = $model->qualifyColumn($model->getKeyName());

        return function (QueryBuilder $sub) use ($ruleTable, $pivotTable, $pivotColumn, $qualifiedKey, $user): void {
            $sub->selectRaw('1')
                ->from($ruleTable, 'ar')
                ->join($pivotTable, "{$pivotTable}.audience_rule_id", '=', 'ar.id')
                ->whereColumn("{$pivotTable}.{$pivotColumn}", '=', $qualifiedKey)
                ->where(function (QueryBuilder $match) use ($user): void {
                    foreach ($this->conditionsFor($user) as $condition) {
                        $match->orWhere($condition);
                    }
                });
        };
    }

    /**
     * Whether a user falls within the given rules.
     *
     * @param  iterable<int, AudienceRule>  $rules
     */
    public function includes(iterable $rules, User $user): bool
    {
        foreach ($rules as $rule) {
            if ($this->ruleMatchesUser($rule, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a user query matching the given rules (union of all rules).
     *
     * @param  iterable<int, AudienceRule>  $rules
     * @return Builder<User>
     */
    public function query(iterable $rules): Builder
    {
        $query = User::query()->whereRaw('1 = 0');

        foreach ($rules as $rule) {
            $query->orWhere(function (Builder $inner) use ($rule): void {
                $this->applyRule($inner, $rule);
            });
        }

        return $query;
    }

    /**
     * Resolve rules to user ids.
     *
     * Only appropriate when the audience is small enough to materialise; see
     * config('daruso.audience.notification_materialisation_limit').
     *
     * @param  iterable<int, AudienceRule>  $rules
     * @return Collection<int, int>
     */
    public function recipientIds(iterable $rules): Collection
    {
        return $this->query($rules)->pluck('users.id')->unique()->values();
    }

    /**
     * Number of users an audience resolves to, computed in the database.
     *
     * @param  iterable<int, AudienceRule>  $rules
     */
    public function countRecipients(iterable $rules): int
    {
        return $this->query($rules)->count();
    }

    /**
     * Distinct values available for a student attribute, used by target pickers.
     *
     * @return Collection<int, string>
     */
    public function distinctValues(AudienceType $type): Collection
    {
        $column = $type->studentColumn();

        if ($column === null) {
            return collect();
        }

        return StudentProfile::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter()
            ->values();
    }

    /**
     * Evaluate a single rule against a concrete user.
     */
    public function ruleMatchesUser(AudienceRule $rule, User $user): bool
    {
        $value = $rule->audience_value;

        if ($value === null && $rule->audience_type !== AudienceType::AllStudents
            && $rule->audience_type !== AudienceType::AllLeaders) {
            return false;
        }

        $profile = $user->relationLoaded('studentProfile')
            ? $user->studentProfile
            : $user->studentProfile()->first();

        return match ($rule->audience_type) {
            AudienceType::AllStudents => $profile !== null,
            AudienceType::AllLeaders => $user->isLeader(),
            AudienceType::Individual => (string) $value === (string) $user->getKey(),
            AudienceType::College => $profile !== null && $profile->college === $value,
            AudienceType::SchoolFaculty => $profile !== null && $profile->school_faculty === $value,
            AudienceType::Programme => $profile !== null && $profile->programme === $value,
            AudienceType::YearOfStudy => $profile !== null && (string) $profile->year_of_study === (string) $value,
            AudienceType::Hostel => $profile !== null && $profile->hostel === $value,
            AudienceType::Ministry => $this->userInMinistry($user, $value),
            AudienceType::Committee => $this->userInCommittee($user, $value),
            AudienceType::Position => $this->userInPosition($user, $value),
            default => false,
        };
    }

    /**
     * OR-ed conditions describing which rules reach a given user.
     *
     * Each condition pairs the rule type/value with the reader's own attributes,
     * so the whole audience check happens inside the database.
     *
     * @return array<int, \Closure(QueryBuilder): void>
     */
    private function conditionsFor(User $user): array
    {
        $userId = $user->getKey();
        $profile = $user->relationLoaded('studentProfile')
            ? $user->studentProfile
            : $user->studentProfile()->first();

        $isLeader = $user->isLeader();
        $leadershipRoles = RoleName::leadershipValues();

        $ministryIds = LeaderAssignment::where('user_id', $userId)->whereNotNull('ministry_id')
            ->pluck('ministry_id')->map(fn ($id): int => (int) $id)->all();

        $committeeIds = CommitteeMember::where('user_id', $userId)->pluck('committee_id')
            ->map(fn ($id): int => (int) $id)->all();

        $positionIds = LeaderAssignment::where('user_id', $userId)->pluck('position_id')
            ->map(fn ($id): int => (int) $id)->all();

        // The literal individual match is always possible.
        $conditions = [
            fn (QueryBuilder $q) => $q->where('ar.audience_type', AudienceType::Individual->value)
                ->where('ar.audience_value', (string) $userId),
        ];

        if ($profile !== null) {
            $conditions[] = fn (QueryBuilder $q) => $q->where('ar.audience_type', AudienceType::AllStudents->value);

            foreach ([
                AudienceType::College->value => 'college',
                AudienceType::SchoolFaculty->value => 'school_faculty',
                AudienceType::Programme->value => 'programme',
                AudienceType::YearOfStudy->value => 'year_of_study',
                AudienceType::Hostel->value => 'hostel',
            ] as $type => $column) {
                $expected = (string) $profile->{$column};

                $conditions[] = fn (QueryBuilder $q) => $q->where('ar.audience_type', $type)
                    ->where('ar.audience_value', $expected);
            }
        }

        if ($isLeader) {
            $conditions[] = fn (QueryBuilder $q) => $q->where('ar.audience_type', AudienceType::AllLeaders->value);
        }

        foreach ([
            [AudienceType::Ministry->value, $ministryIds],
            [AudienceType::Committee->value, $committeeIds],
            [AudienceType::Position->value, $positionIds],
        ] as [$type, $ids]) {
            if ($ids !== []) {
                $conditions[] = fn (QueryBuilder $q) => $q->where('ar.audience_type', $type)
                    ->whereIn('ar.audience_value', array_map('strval', $ids));
            }
        }

        return $conditions;
    }

    /**
     * Apply a rule to a user query as a WHERE group.
     *
     * @param  Builder<User>  $query
     */
    private function applyRule(Builder $query, AudienceRule $rule): void
    {
        $value = $rule->audience_value;
        $leadershipRoles = RoleName::leadershipValues();

        $query->where(function (Builder $inner) use ($rule, $value, $leadershipRoles): void {
            match ($rule->audience_type) {
                AudienceType::AllStudents => $inner->whereHas('studentProfile'),
                AudienceType::AllLeaders => $inner->where(function (Builder $leader) use ($leadershipRoles): void {
                    $leader->whereHas('leaderProfile')
                        ->orWhereHas('roles', fn (Builder $q) => $q->whereIn('name', $leadershipRoles));
                }),
                AudienceType::Individual => $inner->whereKey($value),
                AudienceType::College => $this->whereStudentAttribute($inner, 'college', $value),
                AudienceType::SchoolFaculty => $this->whereStudentAttribute($inner, 'school_faculty', $value),
                AudienceType::Programme => $this->whereStudentAttribute($inner, 'programme', $value),
                AudienceType::YearOfStudy => $this->whereStudentAttribute($inner, 'year_of_study', $value),
                AudienceType::Hostel => $this->whereStudentAttribute($inner, 'hostel', $value),
                AudienceType::Ministry => $inner->whereHas(
                    'leaderAssignments',
                    fn (Builder $q) => $q->where('ministry_id', $value),
                ),
                AudienceType::Committee => $inner->whereHas(
                    'committeeMemberships',
                    fn (Builder $q) => $q->where('committee_id', $value),
                ),
                AudienceType::Position => $inner->whereHas(
                    'leaderAssignments',
                    fn (Builder $q) => $q->where('position_id', $value),
                ),
                default => $inner->whereRaw('1 = 0'),
            };
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    private function whereStudentAttribute(Builder $query, string $column, ?string $value): void
    {
        if ($value === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas('studentProfile', fn (Builder $q) => $q->where($column, $value));
    }

    private function userInMinistry(User $user, string $value): bool
    {
        $ministryId = Ministry::where('name', $value)->orWhere('id', $value)->value('id');

        return $ministryId !== null && LeaderAssignment::where('user_id', $user->getKey())
            ->where('ministry_id', $ministryId)
            ->exists();
    }

    private function userInCommittee(User $user, string $value): bool
    {
        $committeeId = Committee::where('name', $value)->orWhere('id', $value)->value('id');

        return $committeeId !== null && CommitteeMember::where('user_id', $user->getKey())
            ->where('committee_id', $committeeId)
            ->exists();
    }

    private function userInPosition(User $user, string $value): bool
    {
        $positionId = Position::where('name', $value)->orWhere('id', $value)->value('id');

        return $positionId !== null && LeaderAssignment::where('user_id', $user->getKey())
            ->where('position_id', $positionId)
            ->exists();
    }

    /**
     * Pivot table coordinates for an audience-bearing subject.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function pivotFor(string $subject): array
    {
        return match ($subject) {
            Announcement::class => ['audience_rules', 'announcement_audience_rules', 'announcement_id'],
            Meeting::class => ['audience_rules', 'meeting_audience_rules', 'meeting_id'],
            Event::class => ['audience_rules', 'event_audience_rules', 'event_id'],
            default => ['audience_rules', 'notification_audience_rules', 'notification_id'],
        };
    }
}
