<?php

namespace Database\Factories\Domains\Academic\Grading\Models;

use App\Domains\Academic\Grading\Models\TemplateCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateCategoryFactory extends Factory
{
    protected $model = TemplateCategory::class;

    public function definition(): array
    {
        return [
            'grading_template_id' => \App\Domains\Academic\Grading\Models\GradingTemplate::factory(),
            'name' => $this->faker->word,
            'weight' => $this->faker->numberBetween(10, 50),
            'max_raw_score' => 100,
            'calculation_type' => 'sum',
            'is_dynamic_weight' => false,
            'is_locked' => false,
            'pass_required' => false,
            'pass_threshold' => 0,
            'order' => 0,
            'is_final_exam' => false,
        ];
    }
}
