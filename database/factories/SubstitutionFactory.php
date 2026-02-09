<?php

namespace Database\Factories;

use App\Domains\HR\Substitution\Models\Substitution;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Timetable\Models\Timetable;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubstitutionFactory extends Factory
{
    protected $model = Substitution::class;

    public function definition()
    {
        return [
            'date' => $this->faker->date(),
            // Assuming we might need to create these relations if not provided, 
            // but for now keeping it simple or nullable where possible in tests.
            // In a real scenario, we'd factory these too.
            'original_teacher_id' => Teacher::factory(),
            'substitute_teacher_id' => Teacher::factory(),
            'timetable_id' => Timetable::factory(),
            'status' => 'pending',
            'acceptance_status' => 'pending',
            'is_paid' => false,
        ];
    }
}
