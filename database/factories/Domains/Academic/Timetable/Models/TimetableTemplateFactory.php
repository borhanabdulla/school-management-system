<?php

namespace Database\Factories\Domains\Academic\Timetable\Models;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimetableTemplateFactory extends Factory
{
    protected $model = TimetableTemplate::class;

    public function definition(): array
    {
        return [
            'name' => 'قالب ' . $this->faker->word,
            'description' => $this->faker->sentence,
            'is_default' => false,
            'academic_year_id' => \App\Domains\Academic\AcademicYear\Models\AcademicYear::factory(),
        ];
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
        ]);
    }
}
