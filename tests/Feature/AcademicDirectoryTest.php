<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\HR\Teacher\Models\Teacher;

class AcademicDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_teachers_count_correctly()
    {
        // 1. Setup Data
        $year = AcademicYear::factory()->create(['status' => \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active]);
        school()->invalidateYear();

        $grade = Grade::factory()->create();
        $section1 = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'name' => 'أ',
        ]);
        $section2 = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'name' => 'ب',
        ]);

        $subject = Subject::factory()->create();
        $subjectAlt = Subject::factory()->create();
        $teacher1 = Teacher::factory()->create();
        $teacher2 = Teacher::factory()->create();

        // Assign teachers to sections via CourseOfferings
        // Assuming CourseOffering has 'class_section_id' and 'teacher_id'
        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'class_section_id' => $section1->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher1->id,
        ]);

        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'class_section_id' => $section1->id,
            'subject_id' => $subjectAlt->id,
            'teacher_id' => $teacher2->id // Different teacher in same section
        ]);

        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'class_section_id' => $section2->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher1->id // Same teacher1 in different section
        ]);

        // Total unique teachers should be 2 (teacher1, teacher2)

        // 2. Call Service
        $service = new GradeLookupService();
        $grades = $service->getGradesWithStats();

        // 3. Assert
        $this->assertCount(1, $grades);
        $this->assertEquals(2, $grades->first()->teachers_count);
    }
}
