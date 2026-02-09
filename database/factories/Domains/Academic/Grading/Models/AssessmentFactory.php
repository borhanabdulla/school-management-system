<?php

namespace Database\Factories\Domains\Academic\Grading\Models;

use App\Domains\Academic\Grading\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'max_score' => 100,
            'weight' => 100,
            'due_date' => $this->faker->date(),
            'is_published' => true,
        ];
    }
}
