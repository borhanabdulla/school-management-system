<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseOfferingGradeAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_grade_via_class_section(): void
    {
        $year = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active',
        ]);

        $stage = EducationalStage::create(['name' => 'Primary', 'rank' => 1]);
        $grade = Grade::create([
            'name' => 'Grade 1',
            'educational_stage_id' => $stage->id,
            'level_order' => 1,
        ]);

        $section = ClassSection::create([
            'name' => 'A',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $subject = Subject::create([
            'name' => 'Math',
            'code' => 'MATH101',
            'type' => 'theory',
        ]);

        $staff = Staff::create([
            'employee_number' => 'EMP-001',
            'first_name' => 'Sara',
            'last_name' => 'Hassan',
            'phone' => '0500000002',
            'joining_date' => '2024-01-01',
            'status' => 'active',
        ]);
        $teacher = Teacher::create([
            'staff_id' => $staff->id,
            'specialization' => 'Math',
        ]);

        $offering = CourseOffering::create([
            'academic_year_id' => $year->id,
            'term_id' => null,
            'subject_id' => $subject->id,
            'class_section_id' => $section->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->assertSame($grade->id, $offering->grade?->id);
    }
}
