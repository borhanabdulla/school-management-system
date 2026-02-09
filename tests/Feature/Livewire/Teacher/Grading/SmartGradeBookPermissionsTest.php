<?php

namespace Tests\Feature\Livewire\Teacher\Grading;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Models\User;
use App\Livewire\Teacher\Grading\SmartGradeBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SmartGradeBookPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_own_gradebook(): void
    {
        $this->createPermission('marks.view');

        [$user, $teacher] = $this->createTeacherUser();
        $user->givePermissionTo('marks.view');

        [$year, $term] = $this->createAcademicContext();
        $offering = $this->createOfferingForTeacher($teacher, $year->id, $term->id);

        Livewire::actingAs($user)
            ->test(SmartGradeBook::class, ['courseOfferingId' => $offering->id])
            ->assertSet('courseOfferingId', $offering->id);
    }

    public function test_teacher_cannot_view_other_gradebook(): void
    {
        $this->createPermission('marks.view');

        [$user] = $this->createTeacherUser();
        $user->givePermissionTo('marks.view');

        [$year, $term] = $this->createAcademicContext();
        $otherTeacher = Teacher::factory()->create();
        $otherOffering = $this->createOfferingForTeacher($otherTeacher, $year->id, $term->id);

        $this->assertFalse(Gate::forUser($user)->allows('view', $otherOffering));
    }

    public function test_teacher_cannot_add_custom_category_without_curriculum_manage(): void
    {
        $this->createPermission('marks.view');
        $this->createPermission('marks.edit');
        $this->createPermission('curriculum.manage');

        [$user, $teacher] = $this->createTeacherUser();
        $user->givePermissionTo(['marks.view', 'marks.edit']);

        [$year, $term] = $this->createAcademicContext();
        $offering = $this->createOfferingForTeacher($teacher, $year->id, $term->id);

        $this->assertFalse(Gate::forUser($user)->allows('curriculum.manage'));
    }

    public function test_teacher_does_not_see_add_category_button_without_curriculum_manage(): void
    {
        $this->createPermission('marks.view');

        [$user, $teacher] = $this->createTeacherUser();
        $user->givePermissionTo('marks.view');

        [$year, $term] = $this->createAcademicContext();
        $offering = $this->createOfferingForTeacher($teacher, $year->id, $term->id);

        Livewire::actingAs($user)
            ->test(SmartGradeBook::class, ['courseOfferingId' => $offering->id])
            ->assertDontSee('إضافة قسم');
    }

    public function test_teacher_sees_add_category_button_with_curriculum_manage(): void
    {
        $this->createPermission('marks.view');
        $this->createPermission('curriculum.manage');

        [$user, $teacher] = $this->createTeacherUser();
        $user->givePermissionTo(['marks.view', 'curriculum.manage']);

        [$year, $term] = $this->createAcademicContext();
        $offering = $this->createOfferingForTeacher($teacher, $year->id, $term->id);

        Livewire::actingAs($user)
            ->test(SmartGradeBook::class, ['courseOfferingId' => $offering->id])
            ->assertSee('إضافة قسم');
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
        $term = Term::factory()->create(['academic_year_id' => $year->id]);

        return [$year, $term];
    }

    private function createOfferingForTeacher(Teacher $teacher, int $academicYearId, int $termId): CourseOffering
    {
        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $academicYearId,
        ]);

        return CourseOffering::factory()->create([
            'academic_year_id' => $academicYearId,
            'term_id' => $termId,
            'class_section_id' => $classSection->id,
            'teacher_id' => $teacher->id,
        ]);
    }
}
