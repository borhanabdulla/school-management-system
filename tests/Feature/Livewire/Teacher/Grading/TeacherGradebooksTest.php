<?php

namespace Tests\Feature\Livewire\Teacher\Grading;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Models\User;
use App\Livewire\Teacher\Grading\TeacherGradebooks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class TeacherGradebooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_gradebooks_only_show_teacher_offerings(): void
    {
        $this->createPermission('marks.view');

        [$user, $teacher] = $this->createTeacherUser();
        $user->givePermissionTo('marks.view');

        [$year, $term] = $this->createAcademicContext();

        $grade = Grade::factory()->create();
        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $subjectMine = Subject::factory()->create(['name' => 'Subject A']);
        $subjectOther = Subject::factory()->create(['name' => 'Subject B']);

        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subjectMine->id,
            'teacher_id' => $teacher->id,
        ]);

        $otherTeacher = Teacher::factory()->create();
        CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subjectOther->id,
            'teacher_id' => $otherTeacher->id,
        ]);

        Livewire::actingAs($user)
            ->test(TeacherGradebooks::class)
            ->assertSee($subjectMine->name)
            ->assertDontSee($subjectOther->name);
    }

    private function createPermission(string $name): void
    {
        Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array{0: User, 1: Teacher}
     */
    private function createTeacherUser(): array
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);
        $teacher = Teacher::factory()->create(['staff_id' => $staff->id]);

        return [$user, $teacher];
    }

    /**
     * @return array{0: AcademicYear, 1: Term}
     */
    private function createAcademicContext(): array
    {
        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->active()->create([
            'academic_year_id' => $year->id,
        ]);

        return [$year, $term];
    }
}
