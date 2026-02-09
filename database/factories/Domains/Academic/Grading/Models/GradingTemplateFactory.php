<?php

namespace Database\Factories\Domains\Academic\Grading\Models;

use App\Domains\Academic\Grading\Models\GradingTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradingTemplateFactory extends Factory
{
    protected $model = GradingTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'total_max_score' => 100,
            'pass_score' => 50,
            'rounding_rule' => 'nearest_integer',
            'rounding_precision' => 0,
        ];
    }
}
