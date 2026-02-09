<?php

namespace Database\Factories;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = $this->faker->unique()->numberBetween(2000, 2099);
        $endYear = $startYear + 1;

        return [
            'name' => "{$startYear}-{$endYear}",
            'start_date' => Carbon::create($startYear, 9, 1),
            'end_date' => Carbon::create($endYear, 6, 30),
            'status' => AcademicYearStatus::Pending,
            'weekend_days' => json_encode([5, 6]), // Defaults to Fri/Sat
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AcademicYearStatus::Active,
            'start_date' => Carbon::now()->subMonths(3),
            'end_date' => Carbon::now()->addMonths(6),
        ]);
    }
}
