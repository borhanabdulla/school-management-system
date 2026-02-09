<?php

namespace Database\Factories\Domains\Academic\Control\Models;

use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamSeatingFactory extends Factory
{
    protected $model = ExamSeating::class;

    public function definition(): array
    {
        return [
            'exam_session_id' => ExamSession::factory(),
            'student_id' => Student::factory(),
            'committee_id' => null,
            'seat_number' => (string) $this->faker->unique()->numberBetween(1000, 9999),
            'secret_number' => strtoupper($this->faker->unique()->bothify('??####')),
            'is_barred' => false,
            'barred_reason' => null,
            'is_withheld' => false,
            'withhold_reason' => null,
        ];
    }
}
