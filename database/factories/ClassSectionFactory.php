<?php

namespace Database\Factories;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassSectionFactory extends Factory
{
    protected $model = ClassSection::class;

    public function definition(): array
    {
        return [
            'grade_id' => Grade::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => $this->faker->randomElement(['أ', 'ب', 'ج']),
            'max_capacity' => 30,
            'gender_type' => SectionGenderType::Mixed,
            'is_active' => true,
        ];
    }
}
