<?php

namespace Database\Factories;

use App\Domains\Academic\Stage\Models\EducationalStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationalStageFactory extends Factory
{
    protected $model = EducationalStage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'rank' => $this->faker->unique()->numberBetween(1, 10),
            'min_passing_percentage' => 50.00,
            'grading_system' => 'standard',
        ];
    }

    public function elementary(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'المرحلة الابتدائية',
            'rank' => 1,
            'min_passing_percentage' => 50.00,
            'grading_system' => 'standard',
        ]);
    }
}
