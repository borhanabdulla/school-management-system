<?php

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Shared\Models\Address;
use App\Domains\Shared\Models\Attachment;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Models\StudentHealthCondition;
use App\Domains\Academic\Student\Models\StudentPreviousHistory;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Academic\Student\Services\AdmissionNumberService;
use App\Domains\Academic\Student\Services\StudentLookupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use App\Domains\Academic\Student\Enums\StudentStatus;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Shared\Enums\Gender;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use App\Domains\Academic\Student\Exceptions\DuplicateStudentException;
use App\Domains\Academic\Student\Actions\AssignStudentToClassAction;
use App\Domains\Academic\Student\Exceptions\InvalidStudentAgeException;
use App\Domains\Academic\Student\Exceptions\MissingGuardianException;
use App\Domains\Academic\Student\Exceptions\NoActiveAcademicYearException;
use App\Domains\Academic\Student\Services\StudentPlacementSyncService;
use Illuminate\Validation\ValidationException;

class RegisterStudentAction
{
    public function __construct(
        protected CreateInvoiceAction $createInvoiceAction,
        protected AssignStudentToClassAction $assignStudentToClassAction,
        protected StudentPlacementSyncService $placementSyncService,
        protected AdmissionNumberService $admissionNumberService
    ) {
    }

    /**
     * Execute the student registration process.
     *
     * @param StudentRegistrationData $data
     * @return Student
     * @throws DuplicateStudentException
     * @throws InvalidStudentAgeException
     */
    public function execute(StudentRegistrationData $data): Student
    {
        $activeYearId = null;

        $student = DB::transaction(function () use ($data, &$activeYearId) {
            $activeYear = school()->activeYear();
            if (!$activeYear) {
                throw new NoActiveAcademicYearException();
            }

            $activeYear = AcademicYear::query()
                ->whereKey($activeYear->id)
                ->lockForUpdate()
                ->first();

            $activeYearId = $activeYear->id;
            $this->validateData($data);

            // 1. Create Student Record (temporary admission number)
            $student = $this->createStudent($data);// يعمل على إنشاء طالب جديد  

            $admissionNumber = $this->admissionNumberService->generateFor($student);
            $student->updateQuietly(['admission_number' => $admissionNumber]);

            // 2. Handle Guardians
            $this->processGuardians($student, $data->guardians);// يعمل على إنشاء وربط حضرية للطالب

            // 3. Handle Health Conditions
            $this->processHealthConditions($student, $data->health_data);// يعمل على إنشاء وربط حالة صحية للطالب

            // 4. Handle Address
            $this->processAddress($student, $data->address);// يعمل على إنشاء وربط عنوان للطالب

            // 5. Handle Previous History
            $this->processPreviousHistory($student, $data);// يعمل على إنشاء وربط تاريخ سابق للطالب

            // 6. Handle Documents
            $this->processDocuments($student, $data->documents);// يعمل على إنشاء وربط وثائق للطالب

            // 7. Create Initial Enrollment
            $enrollment = $this->createEnrollment($student, $data->grade_id, $data->is_transfer, $activeYear);// يعمل على إنشاء وربط وثائق للطالب

            // Synchronize student's current placement for the active year
            $this->placementSyncService->sync($enrollment);

            // 8. Assign to Class Section (if selected)
            if ($data->class_section_id) {
                $section = ClassSection::findOrFail($data->class_section_id);// 
                $this->assignStudentToClassAction->execute($student, $section);
            }

            // 9. Create Invoice
            if ($data->create_invoice) {
                $this->createInvoice($student, $data->grade_id, $activeYear);
            }

            return $student;
        });

        if ($activeYearId) {
            StudentLookupService::clearCache($activeYearId);
        }
        StudentLookupService::clearCache(null);

        return $student;
    }

    protected function validateData(StudentRegistrationData $data): void
    {
        // Check for duplicate national_id
        if (!empty($data->student['national_id'])) {
            $existingStudent = Student::where('national_id', $data->student['national_id'])->first();
            if ($existingStudent) {
                throw new DuplicateStudentException(
                    $data->student['national_id'],
                    $existingStudent
                );
            }
        }

        // Check age for grade
        $grade = Grade::find($data->grade_id);
        if ($grade && $grade->min_age && $grade->max_age) {
            $age = Carbon::parse($data->student['date_of_birth'])->age;
            if ($age < $grade->min_age || $age > $grade->max_age) {
                throw new InvalidStudentAgeException(
                    $age,
                    $grade->min_age,
                    $grade->max_age,
                    $grade->name
                );
            }
        }
    }



    protected function createStudent(StudentRegistrationData $data): Student
    {
        return Student::create([
            'admission_number' => 'TMP-' . Str::uuid(),
            'first_name_ar' => $data->student['first_name_ar'],
            'family_name_ar' => $data->student['family_name_ar'],
            'date_of_birth' => $data->student['date_of_birth'],
            'gender' => $data->student['gender'],
            'nationality_id' => $data->student['nationality_id'] ?? null,
            'national_id' => $data->student['national_id'] ?? null,
            'blood_type' => $data->student['blood_type'] ?? null,
            'current_grade_id' => $data->grade_id,
            'current_class_section_id' => $data->class_section_id,
            'status' => StudentStatus::Active->value,
        ]);
    }

    protected function processGuardians(Student $student, array $guardiansData): void
    {
        if (empty($guardiansData)) {
            throw new MissingGuardianException();
        }

        foreach ($guardiansData as $guardianInfo) {
            $guardian = $this->resolveGuardian($guardianInfo);
            $this->syncGuardianPivot($student, $guardian, $guardianInfo);
        }
    }

    private function resolveGuardian(array $guardianInfo): Guardian
    {
        if (!empty($guardianInfo['id'])) {
            $guardian = Guardian::find($guardianInfo['id']);
            if (!$guardian) {
                throw ValidationException::withMessages([
                    'guardians' => 'الولي المحدد غير موجود.'
                ]);
            }

            return $guardian;
        }

        if (!empty($guardianInfo['data'])) {
            $guardian = $this->findGuardianByData($guardianInfo['data']);

            if (!$guardian) {
                return Guardian::create([
                    'first_name' => $guardianInfo['data']['first_name'],
                    'last_name' => $guardianInfo['data']['last_name'],
                    'national_id' => $guardianInfo['data']['national_id'] ?? null,
                    'phone' => $guardianInfo['data']['phone'],
                    'nationality_id' => $guardianInfo['data']['nationality_id'] ?? null,
                    'preferred_language' => $guardianInfo['data']['preferred_language'] ?? 'ar',
                ]);
            }

            return $guardian;
        }

        throw ValidationException::withMessages([
            'guardians' => 'بيانات ولي الأمر غير مكتملة.'
        ]);
    }

    private function syncGuardianPivot(Student $student, Guardian $guardian, array $guardianInfo): void
    {
        $relationship = $this->mapRelationship($guardianInfo['relationship'] ?? GuardianRelationship::Father->value);

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'relationship' => $relationship,
                'is_financial_sponsor' => $guardianInfo['is_financial_sponsor'] ?? false,
                'is_emergency_contact' => $guardianInfo['is_emergency_contact'] ?? false,
                'lives_with' => $guardianInfo['lives_with'] ?? true,
                'has_portal_access' => $guardianInfo['has_portal_access'] ?? true,
            ],
        ]);
    }

    private function findGuardianByData(array $data): ?Guardian
    {
        if (!empty($data['national_id'])) {
            $guardian = Guardian::where('national_id', $data['national_id'])->first();
            if ($guardian) {
                return $guardian;
            }
        }

        if (!empty($data['phone'])) {
            return Guardian::where('phone', $data['phone'])->first();
        }

        return null;
    }

    private function mapRelationship(string $relationship): string
    {
        $allowed = [
            GuardianRelationship::Father->value,
            GuardianRelationship::Mother->value,
            GuardianRelationship::Brother->value,
            GuardianRelationship::Uncle->value,
            GuardianRelationship::Other->value,
        ];

        if (in_array($relationship, $allowed, true)) {
            return $relationship;
        }

        return GuardianRelationship::Other->value;
    }

    protected function processHealthConditions(Student $student, array $healthData): void
    {
        if (!empty($healthData)) {
            foreach ($healthData as $health) {
                if (!empty($health['condition_id'])) {
                    StudentHealthCondition::create([
                        'student_id' => $student->id,
                        'health_condition_type_id' => $health['condition_id'],
                        'notes' => $health['notes'] ?? null,
                    ]);
                }
            }
        }
    }

    protected function processAddress(Student $student, array $addressData): void
    {
        if (!empty($addressData)) {
            $data = [
                'addressable_type' => Student::class,
                'addressable_id' => $student->id,
                'city' => $addressData['city'],
                'district' => $addressData['district'],
                'street_name' => $addressData['street_name'],
                'building_number' => $addressData['building_number'] ?? null,
                'is_primary' => true,
            ];

            if (!empty($addressData['google_maps_link'])) {
                preg_match('/@?(-?\d+\.\d+),(-?\d+\.\d+)/', $addressData['google_maps_link'], $matches);
                if (count($matches) === 3) {
                    $data['latitude'] = $matches[1];
                    $data['longitude'] = $matches[2];
                }
            }

            Address::create($data);
        }
    }

    protected function processPreviousHistory(Student $student, StudentRegistrationData $data): void
    {
        if ($data->is_transfer && !empty($data->previous_history)) {
            StudentPreviousHistory::create([
                'student_id' => $student->id,
                'school_name' => $data->previous_history['school_name'],
                'previous_curriculum' => $data->previous_history['previous_curriculum'] ?? null,
                'last_grade_completed' => $data->previous_history['last_grade_completed'] ?? null,
                'completion_year' => $data->previous_history['completion_year'] ?? null,
                'last_gpa' => $data->previous_history['last_gpa'] ?? null,
                'reason_for_transfer' => $data->previous_history['reason_for_transfer'] ?? null,
            ]);
        }
    }

    protected function processDocuments(Student $student, array $documents): void
    {
        if (!empty($documents)) {
            foreach ($documents as $document) {
                if (!empty($document['file']) && !empty($document['type'])) {
                    $file = $document['file'];
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();

                    $filename = $student->id . '_' . $document['type'] . '_' . time() . '.' . $extension;
                    $path = $file->storeAs('students/documents', $filename, 'public');

                    $attachment = Attachment::create([
                        'attachable_type' => Student::class,
                        'attachable_id' => $student->id,
                        'document_type' => $document['type'],
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'original_name' => $originalName,
                        'is_verified' => false,
                    ]);
                }
            }
        }
    }

    protected function createEnrollment(Student $student, int $gradeId, bool $isTransfer, AcademicYear $activeYear): StudentEnrollment
    {
        return StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $activeYear->id,
            'grade_id' => $gradeId,
            'class_section_id' => null,
            'enrollment_type' => $isTransfer ? 'transfer_in' : 'new',
            'status' => EnrollmentStatus::Active,
            'enrollment_date' => now(),
        ]);
    }

    protected function createInvoice(Student $student, int $gradeId, AcademicYear $activeYear): void
    {
        $this->createInvoiceAction->execute(
            InvoiceData::fromArray([
                'student_id' => $student->id,
                'academic_year_id' => $activeYear->id,
                'grade_id' => $gradeId,
            ])
        );
    }
}
