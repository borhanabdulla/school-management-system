<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\Student\Actions\DeleteGuardianAction;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Infrastructure\Exceptions\CannotDeleteException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteGuardianActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_guardian_without_students(): void
    {
        $guardian = Guardian::factory()->create();

        app(DeleteGuardianAction::class)->execute($guardian);

        $this->assertDatabaseMissing('guardians', ['id' => $guardian->id]);
    }

    public function test_it_blocks_deletion_when_students_linked(): void
    {
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();

        $guardian->students()->attach($student->id, [
            'relationship' => GuardianRelationship::Father->value,
            'is_financial_sponsor' => true,
        ]);

        $this->expectException(CannotDeleteException::class);

        app(DeleteGuardianAction::class)->execute($guardian);
    }
}
