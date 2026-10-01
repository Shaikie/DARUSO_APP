<?php

namespace Database\Factories;

use App\Enums\AudienceType;
use App\Models\AudienceRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AudienceRule>
 */
class AudienceRuleFactory extends Factory
{
    protected $model = AudienceRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'audience_type' => AudienceType::AllStudents->value,
            'audience_value' => null,
        ];
    }

    public function type(AudienceType $type, ?string $value = null): static
    {
        return $this->state(fn (): array => [
            'audience_type' => $type->value,
            'audience_value' => $value,
        ]);
    }
}
