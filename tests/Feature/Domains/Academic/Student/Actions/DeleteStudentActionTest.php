<?php

namespace Tests\Feature\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Actions\DeleteStudentAction;
use App\Domains\Academic\Student\Exceptions\StudentDeleteBlockedException;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteStudentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_student_and_related_data(): void
    {
        Storage::fake('public');

        $student = Student::factory()->create([
            'profile_photo_path' => 'photos/student.jpg',
        ]);

        // Create a user for the student
        $user = \App\Domains\Shared\Models\User::factory()->create();
        $student->user_id = $user->id;
        $student->save();

        Storage::disk('public')->put('photos/student.jpg', 'content');

        $action = app(DeleteStudentAction::class);
        $action->execute($student);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        Storage::disk('public')->assertMissing('photos/student.jpg');
    }

    public function test_it_blocks_deletion_if_invoices_exist(): void
    {
        $student = Student::factory()->create();
        $year = \App\Domains\Academic\AcademicYear\Models\AcademicYear::factory()->create();

        Invoice::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'invoice_number' => 'INV-001',
            'total_amount' => 100,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $this->expectException(StudentDeleteBlockedException::class);
        $this->expectExceptionMessage('لديه سجلات مالية (فواتير)');

        $action = app(DeleteStudentAction::class);
        $action->execute($student);
    }
}

