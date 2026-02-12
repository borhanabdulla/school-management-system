<?php

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Academic\Student\Events\StudentAssignedToClass;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Validation\ValidationException;
use App\Domains\Academic\ClassSection\Exceptions\ClassroomFullException;
use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Domains\Academic\Student\Services\StudentPlacementSyncService;

class AssignStudentToClassAction
{
    public function __construct(
        protected StudentPlacementSyncService $placementSyncService
    ) {
    }

    /**
     * Assign a student to a specific class section.
     *
     * @param Student $student
     * @param ClassSection $section
     * @return StudentEnrollment
     * @throws ValidationException
     * @throws ClassroomFullException
     */
    public function execute(Student $student, ClassSection $section): StudentEnrollment
    {
        // 1. Get current active academic year (Single Source of Truth)
        $currentYear = school()->activeYear();

        if (!$currentYear) {
            throw new \App\Infrastructure\Exceptions\InvalidOperationException(
                'لا يمكن تعيين طالب لشعبة بدون وجود سنة دراسية نشطة',
                'assign_student_to_class',
                'no_active_year'
            );
        }

        // 🛡️ PR-1: Guard against closed year
        app(AcademicWriteGuard::class)->assertYearNotClosed($currentYear->id);

        if ((int) $section->academic_year_id !== (int) $currentYear->id) {
            throw ValidationException::withMessages([
                'section' => 'لا يمكن تعيين الطالب لشعبة من سنة دراسية مختلفة.'
            ]);
        }

        // 2. Get or create enrollment for this year
        // We assume the student might already have an enrollment (e.g. created during registration without section)
        $enrollmentType = $student->enrollments()->exists() ? 'returning' : 'new';
        $enrollment = StudentEnrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $currentYear->id,
            ],
            [
                'grade_id' => $section->grade_id, // Default to section's grade if creating new
                'enrollment_type' => $enrollmentType,
                'status' => EnrollmentStatus::Active,
                'enrollment_date' => now(),
            ]
        );

        // 3. Validation: Grade Match
        if ($enrollment->grade_id !== $section->grade_id) {
            throw ValidationException::withMessages([
                'section' => 'Section does not belong to the student\'s current grade.'
            ]);
        }

        // 4. Validation: Gender Compatibility
        if ($section->gender_type !== SectionGenderType::Mixed) {
            $studentGender = $student->gender instanceof \UnitEnum ? $student->gender : \App\Domains\Shared\Enums\Gender::tryFrom($student->gender);

            $isCompatible = match ($section->gender_type) {
                SectionGenderType::Boys => $studentGender === \App\Domains\Shared\Enums\Gender::Male,
                SectionGenderType::Girls => $studentGender === \App\Domains\Shared\Enums\Gender::Female,
                default => true,
            };

            if (!$isCompatible) {
                throw ValidationException::withMessages([
                    'section' => 'Section gender type does not match student gender.'
                ]);
            }
        }

        // 5. Validation: Capacity Check
        $currentCount = StudentEnrollment::where('class_section_id', $section->id)
            ->where('academic_year_id', $currentYear->id)
            ->count();

        if ($currentCount >= $section->max_capacity) {
            throw new ClassroomFullException($section);
        }

        // 6. Update Enrollment
        $enrollment->update([
            'class_section_id' => $section->id,
        ]);

        $this->placementSyncService->sync($enrollment);

        // 7. Dispatch Event
        StudentAssignedToClass::dispatch($student, $section);

        // 8. Invalidate Cache
        \App\Domains\Academic\Grade\Services\GradeLookupService::invalidateCache();
        StudentLookupService::clearCache($currentYear->id);
        StudentLookupService::clearCache(null);

        return $enrollment;
    }
}
