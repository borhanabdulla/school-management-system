<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Actions\AssignStudentToClassAction;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Enums\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Infrastructure\Context\AcademicContextService;
use Tests\TestCase;

class StudentPlacementSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_student_updates_current_placement_for_active_year(): void
    {
        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'gender_type' => SectionGenderType::Mixed,
        ]);

        $student = Student::factory()->create([
            'gender' => Gender::Male->value,
        ]);

        $action = app(AssignStudentToClassAction::class);
        $action->execute($student, $section);

        $student->refresh();
        $this->assertSame($grade->id, $student->current_grade_id);
        $this->assertSame($section->id, $student->current_class_section_id);
    }

    /** @test */
    public function test_assign_student_works_when_cache_is_stale(): void
    {
        $staleYear = AcademicYear::factory()->create(['status' => 'pending']);
        $year = AcademicYear::factory()->active()->create();

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $staleYear, 3600);

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'gender_type' => SectionGenderType::Mixed,
        ]);

        $student = Student::factory()->create([
            'gender' => Gender::Male->value,
        ]);

        $action = app(AssignStudentToClassAction::class);
        $action->execute($student, $section);

        $student->refresh();
        $this->assertSame($grade->id, $student->current_grade_id);
        $this->assertSame($section->id, $student->current_class_section_id);
    }
}
