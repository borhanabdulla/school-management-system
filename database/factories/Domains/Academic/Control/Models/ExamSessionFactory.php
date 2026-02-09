<?php

namespace Database\Factories\Domains\Academic\Control\Models;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExamSessionFactory extends Factory
{
    protected $model = ExamSession::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => Term::factory()->state(function (array $attributes) {
                return ['academic_year_id' => $attributes['academic_year_id']];
            }),
            'name' => 'اختبارات ' . $this->faker->unique()->word,
            'start_date' => Carbon::now()->subDays(3),
            'end_date' => Carbon::now()->addDays(3),
            'status' => 'setup',
            'is_active' => false,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'is_active' => true,
        ]);
    }
}
