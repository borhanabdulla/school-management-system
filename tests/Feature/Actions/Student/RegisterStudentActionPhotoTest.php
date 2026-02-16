<?php

namespace Tests\Feature\Actions\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegisterStudentActionPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_student_with_photo(): void
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('student.jpg');

        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();
        $grade = Grade::factory()->create();

        $data = new StudentRegistrationData(
            student: [
                'first_name_ar' => 'Ahmed',
                'family_name_ar' => 'Photo',
                'date_of_birth' => '2015-01-01',
                'gender' => 'male',
                'nationality_id' => null,
                'blood_type' => null,
                'national_id' => '1010101010',
            ],
            guardians: [
                [
                    'data' => [
                        'first_name' => 'Parent',
                        'last_name' => 'One',
                        'national_id' => 'G-100',
                        'phone' => '0500000001',
                        'nationality_id' => null,
                    ],
                    'relationship' => GuardianRelationship::Father->value,
                    'is_financial_sponsor' => true,
                    'is_emergency_contact' => true,
                    'lives_with' => true,
                    'has_portal_access' => true,
                ],
            ],
            grade_id: $grade->id,
            class_section_id: null,
            health_data: [],
            address: [],
            documents: [],
            photo: $photo,
            is_transfer: false,
            previous_history: null,
            create_invoice: false
        );

        $action = app(RegisterStudentAction::class);
        $student = $action->execute($data);

        $this->assertNotNull($student->profile_photo_path);
        Storage::disk('public')->assertExists($student->profile_photo_path);
    }
}
