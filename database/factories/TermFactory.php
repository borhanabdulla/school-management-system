<?php

namespace Database\Factories;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TermFactory extends Factory
{
    protected $model = Term::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => $this->faker->word,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
            'order_index' => 1,
            'status' => TermStatus::Pending,
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TermStatus::Active,
        ]);
    }

    public function firstTerm(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'الفصل الدراسي الأول',
            'order_index' => 1,
            'start_date' => Carbon::now()->subMonths(3),
            'end_date' => Carbon::now()->addMonths(1),
        ]);
    }

    public function secondTerm(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'الفصل الدراسي الثاني',
            'order_index' => 2,
            'start_date' => Carbon::now()->addMonths(2),
            'end_date' => Carbon::now()->addMonths(6),
            'status' => TermStatus::Pending,
        ]);
    }
}
