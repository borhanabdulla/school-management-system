<?php

namespace Tests\Feature\Domains\Academic\Reporting;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Reporting\Services\ReportCardBuilder;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Exceptions\ResultsBlockedByFinanceException;
use App\Domains\Finance\Models\FeeStructure;
use App\Domains\Finance\Models\FeeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardFinancialBlockTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;
    private Term $term;
    private Student $student;
    private Guardian $sponsor;
    private CreateInvoiceAction $createInvoiceAction;
    private ReportCardBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = AcademicYear::factory()->create(['status' => 'active']);
        $this->term = Term::factory()->create(['academic_year_id' => $this->year->id]);
        $this->grade = Grade::factory()->create();
        $this->student = Student::factory()->create(['current_grade_id' => $this->grade->id]);
        $this->sponsor = Guardian::factory()->create();
        $this->student->guardians()->attach($this->sponsor->id, ['is_financial_sponsor' => true, 'relationship' => 'father']);

        // Fees
        $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);
        FeeStructure::create([
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
            'fee_type_id' => $feeType->id,
            'amount' => 1000.00,
            'due_date' => now(),
        ]);

        $this->createInvoiceAction = app(CreateInvoiceAction::class);
        $this->builder = app(ReportCardBuilder::class);
    }

    /** @test */
    public function build_single_throws_exception_if_student_is_not_cleared()
    {
        // 1. Create Invoice (Unpaid)
        $this->createInvoiceAction->execute(\App\Domains\Finance\Data\InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        // 2. Expect Exception
        $this->expectException(ResultsBlockedByFinanceException::class);
        $this->builder->build($this->student->id, $this->term->id);
    }

    /** @test */
    public function build_batch_returns_blocked_status_for_uncleared_student()
    {
        // 1. Create Invoice (Unpaid)
        $invoice = $this->createInvoiceAction->execute(\App\Domains\Finance\Data\InvoiceData::fromArray([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->grade->id,
        ]));

        // 2. Build for batch
        $reports = $this->builder->buildForStudents(collect([$this->student]), $this->term->id);

        // 3. Verify
        expect($reports)->not->toBeEmpty();
        expect($reports[$this->student->id]['meta']['is_blocked'])->toBeTrue();
        expect($reports[$this->student->id]['subjects'])->toBeEmpty();
    }
}
