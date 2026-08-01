<?php

namespace Tests\Feature\Domains\Academic\Student;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Data\StudentDirectoryFilterData;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_stats_cache_bumps_version_after_clear_cache(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $student = Student::factory()->create([
            'current_grade_id' => $grade->id,
            'current_class_section_id' => $section->id,
            'status' => 'active',
        ]);
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
        ]);

        $service = app(StudentLookupService::class);
        $filter = new StudentDirectoryFilterData(
            academicYearId: $year->id,
            gradeId: $grade->id,
            sectionId: $section->id,
            status: 'active'
        );

        $stats = $service->getDirectoryStats($filter);
        $this->assertSame(1, $stats['total_students']);

        $secondStudent = Student::factory()->create([
            'current_grade_id' => $grade->id,
            'current_class_section_id' => $section->id,
            'status' => 'active',
        ]);
        StudentEnrollment::factory()->create([
            'student_id' => $secondStudent->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
        ]);

        $statsCached = $service->getDirectoryStats($filter);
        $this->assertSame(1, $statsCached['total_students']);

        StudentLookupService::clearCache($year->id);

        $statsAfterClear = $service->getDirectoryStats($filter);
        $this->assertSame(2, $statsAfterClear['total_students']);
    }

    public function test_financial_status_uses_invoice_status_values(): void
    {
        $year = AcademicYear::factory()->active()->create();

        $studentWithUnpaid = Student::factory()->create();
        Invoice::create([
            'invoice_number' => 'INV-1001',
            'student_id' => $studentWithUnpaid->id,
            'academic_year_id' => $year->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 100,
            'paid_amount' => 20,
            'status' => InvoiceStatus::Unpaid->value,
        ]);
        Invoice::create([
            'invoice_number' => 'INV-1002',
            'student_id' => $studentWithUnpaid->id,
            'academic_year_id' => $year->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 50,
            'paid_amount' => 50,
            'status' => InvoiceStatus::Paid->value,
        ]);

        $studentPaid = Student::factory()->create();
        Invoice::create([
            'invoice_number' => 'INV-2001',
            'student_id' => $studentPaid->id,
            'academic_year_id' => $year->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 80,
            'paid_amount' => 80,
            'status' => InvoiceStatus::Paid->value,
        ]);

        $service = app(StudentLookupService::class);
        $financial = $service->getFinancialStatus($studentWithUnpaid);
        $this->assertSame(InvoiceStatus::Unpaid->value, $financial['status']);
        $this->assertEquals(80.0, $financial['amount']);

        $financialPaid = $service->getFinancialStatus($studentPaid);
        $this->assertSame(InvoiceStatus::Paid->value, $financialPaid['status']);
        $this->assertEquals(0, $financialPaid['amount']);
    }

    public function test_find_many_with_guardians_eager_loads_relations(): void
    {
        $student = Student::factory()->create();

        $guardian = new \App\Domains\Academic\Student\Models\Guardian();
        $guardian->first_name = 'Test';
        $guardian->last_name = 'Guardian';
        $guardian->national_id = '1234567890';
        $guardian->phone = '0500000000';
        $guardian->save();

        $student->guardians()->attach($guardian, ['relationship' => 'father']);

        $service = app(StudentLookupService::class);
        $results = $service->findManyWithGuardians([$student->id]);

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->relationLoaded('guardians'));
        $this->assertEquals($guardian->id, $results->first()->guardians->first()->id);
    }

    public function test_get_by_class_section_returns_active_students_ordered(): void
    {
        $section = ClassSection::factory()->create();

        $student2 = Student::factory()->create([
            'current_class_section_id' => $section->id,
            'first_name_ar' => 'B',
            'status' => 'active'
        ]);

        $student1 = Student::factory()->create([
            'current_class_section_id' => $section->id,
            'first_name_ar' => 'A',
            'status' => 'active'
        ]);

        $inactiveStudent = Student::factory()->create([
            'current_class_section_id' => $section->id,
            'status' => 'inactive'
        ]);

        foreach ([$student1, $student2, $inactiveStudent] as $student) {
            $student->enrollments()->create([
                'class_section_id' => $section->id,
                'academic_year_id' => $section->academic_year_id,
                'grade_id' => $section->grade_id,
                'status' => 'active',
                'enrollment_date' => now(),
                'enrollment_type' => 'new',
            ]);
        }

        $service = app(StudentLookupService::class);
        $results = $service->getByClassSection($section->id, $section->academic_year_id);

        $this->assertCount(2, $results);
        $this->assertEquals($student1->id, $results->first()->id);
        $this->assertEquals($student2->id, $results->last()->id);
        $this->assertFalse($results->contains($inactiveStudent));
    }
}
