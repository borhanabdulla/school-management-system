<?php

namespace Database\Factories\Domains\Academic\Student\Models;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_id' => Grade::factory(),
            'class_section_id' => ClassSection::factory(),
            'enrollment_date' => $this->faker->date(),
            'enrollment_type' => 'new',
            'status' => 'active',
        ];
    }
}
