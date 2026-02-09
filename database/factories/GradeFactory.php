<?php

namespace Database\Factories;

use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Models\EducationalStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'educational_stage_id' => EducationalStage::factory(),
            'name' => $this->faker->word,
            'level_order' => $this->faker->numberBetween(1, 12),
            'min_age' => 6,
            'max_age' => 18,
        ];
    }
}
