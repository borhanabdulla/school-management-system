<?php

namespace Database\Factories\Domains\Academic\Student\Models;

use App\Domains\Academic\Student\Models\StudentMark;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentMarkFactory extends Factory
{
    protected $model = StudentMark::class;

    public function definition(): array
    {
        return [
            'raw_score' => $this->faker->numberBetween(0, 100),
            'is_missing' => false,
            'is_excused' => false,
        ];
    }
}
