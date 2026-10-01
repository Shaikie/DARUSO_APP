<?php

namespace App\Services;

use App\Enums\AudienceType;
use App\Models\AudienceRule;
use App\Models\Committee;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Creates and validates audience rules from request input.
 *
 * Centralising this keeps the vocabulary consistent across announcements,
 * notifications, meetings and events, and guarantees every rule references a
 * value that actually exists.
 */
class AudienceRuleRepository
{
    /**
     * Build (but do not persist) rules from validated request data.
     *
     * @param  array<string, mixed>  $data  Keys are audience type values; values
     *                                      are arrays of selected values, or
     *                                      booleans for "all" types.
     * @return Collection<int, array{audience_type: AudienceType, audience_value: ?string}>
     */
    public function build(array $data): Collection
    {
        $rules = collect();

        foreach ($data as $type => $value) {
            if (! in_array($type, AudienceType::values(), true)) {
                continue;
            }

            $audienceType = AudienceType::from($type);

            if (! $audienceType->requiresValue()) {
                if (filter_var($value, FILTER_VALIDATE_BOOL)) {
                    $rules->push(['audience_type' => $audienceType, 'audience_value' => null]);
                }

                continue;
            }

            foreach ((array) $value as $item) {
                $item = trim((string) $item);

                if ($item !== '') {
                    $rules->push(['audience_type' => $audienceType, 'audience_value' => $item]);
                }
            }
        }

        return $rules;
    }

    /**
     * Persist rules, reusing existing records for the same type/value pair.
     *
     * Rules are shared vocabulary rather than per-message data, so reusing them
     * keeps the `audience_rules` table small across the whole system.
     *
     * @param  Collection<int, array{audience_type: AudienceType, audience_value: ?string}>  $rules
     * @return Collection<int, AudienceRule>
     */
    public function persist(Collection $rules): Collection
    {
        return $rules->map(fn (array $rule): AudienceRule => AudienceRule::firstOrCreate([
            'audience_type' => $rule['audience_type']->value,
            'audience_value' => $rule['audience_value'],
        ]))->values();
    }

    /**
     * Available target values per audience type, for form selects.
     *
     * @return array<string, array<int, string>>
     */
    public function options(): array
    {
        $studentAttributes = StudentProfile::query()
            ->select('college', 'school_faculty', 'programme', 'hostel')
            ->limit(1000)
            ->get();

        return [
            AudienceType::College->value => $studentAttributes->pluck('college')->filter()->unique()->sort()->values()->all(),
            AudienceType::SchoolFaculty->value => $studentAttributes->pluck('school_faculty')->filter()->unique()->sort()->values()->all(),
            AudienceType::Programme->value => $studentAttributes->pluck('programme')->filter()->unique()->sort()->values()->all(),
            AudienceType::Hostel->value => $studentAttributes->pluck('hostel')->filter()->unique()->sort()->values()->all(),
            AudienceType::YearOfStudy->value => StudentProfile::query()
                ->distinct()->orderBy('year_of_study')->pluck('year_of_study')
                ->map(fn (int $year): string => (string) $year)->all(),
            AudienceType::Ministry->value => Ministry::orderBy('name')->pluck('name')->all(),
            AudienceType::Committee->value => Committee::orderBy('name')->pluck('name')->all(),
            AudienceType::Position->value => Position::orderBy('hierarchy_level')->pluck('name')->all(),
            AudienceType::Individual->value => User::orderBy('name')->limit(500)->pluck('name')->all(),
        ];
    }

    /**
     * Human-readable summary of a set of rules.
     *
     * @param  Collection<int, AudienceRule>  $rules
     * @return Collection<int, string>
     */
    public function describe(Collection $rules): Collection
    {
        return $rules->map(function (AudienceRule $rule): string {
            $type = $rule->type();

            return $type->requiresValue()
                ? $type->label().': '.$rule->audience_value
                : $type->label();
        });
    }
}
