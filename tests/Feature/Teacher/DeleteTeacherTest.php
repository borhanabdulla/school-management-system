<?php

namespace Tests\Feature\Teacher;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Substitution\Models\Substitution;
use App\Domains\HR\Teacher\Actions\DeleteTeacherAction;
use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DomainException;

class DeleteTeacherTest extends TestCase
{
    use RefreshDatabase;

    private DeleteTeacherAction $deleteTeacherAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteTeacherAction = app(DeleteTeacherAction::class);
    }

    /** @test */
    public function it_can_delete_teacher_with_no_relations()
    {
        $staff = Staff::factory()->create();
        $teacher = Teacher::factory()->create(['staff_id' => $staff->id]);

        $this->deleteTeacherAction->execute($teacher);

        $this->assertModelMissing($teacher);
        // Ensure staff record remains (as per action logic)
        $this->assertModelExists($staff);
    }

    /** @test */
    public function it_cannot_delete_teacher_linked_to_course_offerings()
    {
        $teacher = Teacher::factory()->create();
        CourseOffering::factory()->create(['teacher_id' => $teacher->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('لا يمكن حذف المعلم لارتباطه بمقررات دراسية/جداول حالية.');

        $this->deleteTeacherAction->execute($teacher);
    }

    /** @test */
    public function it_cannot_delete_teacher_linked_as_original_in_substitution()
    {
        $teacher = Teacher::factory()->create();
        Substitution::factory()->create(['original_teacher_id' => $teacher->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('لا يمكن حذف المعلم لوجود سجلات بدلاء (كمعلم غائب).');

        $this->deleteTeacherAction->execute($teacher);
    }

    /** @test */
    public function it_cannot_delete_teacher_linked_as_substitute_in_substitution()
    {
        $teacher = Teacher::factory()->create();
        Substitution::factory()->create(['substitute_teacher_id' => $teacher->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('لا يمكن حذف المعلم لوجود سجلات بدلاء (كمعلم بديل).');

        $this->deleteTeacherAction->execute($teacher);
    }
}
