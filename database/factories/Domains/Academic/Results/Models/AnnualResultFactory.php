<?php

namespace Database\Factories\Domains\Academic\Results\Models;

use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnualResultFactory extends Factory
{
    protected $model = AnnualResult::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'term1_total' => $this->faker->numberBetween(40, 50),
            'term1_max' => 50,
            'term2_total' => $this->faker->numberBetween(40, 50),
            'term2_max' => 50,
            'annual_total' => function (array $attributes) {
                return $attributes['term1_total'] + $attributes['term2_total'];
            },
            'annual_max' => 100,
            'percentage' => function (array $attributes) {
                return ($attributes['annual_total'] / $attributes['annual_max']) * 100;
            },
            'failed_subjects' => [],
            'failed_count' => 0,
            'decision' => 'pass', // pending, pass, fail, conditional
            'grade_label' => 'ممتاز',
            'rank' => $this->faker->numberBetween(1, 30),
            'processed_at' => now(),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'decision' => 'pending',
                'processed_at' => null,
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'term1_total' => 20,
                'term2_total' => 20,
                'annual_total' => 40,
                'percentage' => 40,
                'decision' => 'fail',
                'grade_label' => 'راسب',
                'failed_count' => 2,
                'failed_subjects' => ['Math', 'Science'],
            ];
        });
    }
}
