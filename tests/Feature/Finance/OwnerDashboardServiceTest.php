<?php

namespace Tests\Feature\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Services\OwnerDashboardService;
use App\Domains\Finance\Services\ReceivablesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OwnerDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Bind mocks or real services? Real services for integration test.
        $this->service = app(OwnerDashboardService::class);
    }

    public function test_receivables_logic_separates_years_correctly()
    {
        // Setup
        $yearA = AcademicYear::factory()->create(['status' => 'closed']);
        $yearB = AcademicYear::factory()->create(['status' => 'active']);
        $student = Student::factory()->create();

        // Invoice in Year A: 1000 Total, 200 Paid -> 800 Outstanding
        Invoice::factory()->create([
            'academic_year_id' => $yearA->id,
            'student_id' => $student->id,
            'total_amount' => 1000,
            'paid_amount' => 200,
            'status' => \App\Domains\Finance\Enums\InvoiceStatus::Unpaid
        ]);

        // Invoice in Year B: 2000 Total, 0 Paid -> 2000 Outstanding
        Invoice::factory()->create([
            'academic_year_id' => $yearB->id,
            'student_id' => $student->id,
            'total_amount' => 2000,
            'paid_amount' => 0,
            'status' => \App\Domains\Finance\Enums\InvoiceStatus::Unpaid
        ]);

        // Act 1: Get data with specific year (Year A)
        $dataA = $this->service->getDashboardData('month', $yearA->id);

        // Act 2: Get data with specific year (Year B)
        $dataB = $this->service->getDashboardData('month', $yearB->id);

        // Act 3: Get data without specific year (All)
        // Note: OwnerDashboardService always returns 'receivables_all'. logic for 'receivables_year' depends on arg.
        $dataAll = $this->service->getDashboardData('month', null);

        // Assertions

        // 1. Receivables All should be same in all cases (global truth)
        $expectedTotalOutstanding = 800 + 2000;
        $this->assertEquals($expectedTotalOutstanding, $dataA['receivables_all']['total_outstanding'], 'All years outstanding mismatch in Data A');
        $this->assertEquals($expectedTotalOutstanding, $dataB['receivables_all']['total_outstanding'], 'All years outstanding mismatch in Data B');
        $this->assertEquals($expectedTotalOutstanding, $dataAll['receivables_all']['total_outstanding'], 'All years outstanding mismatch in Data All');

        // 2. Receivables Specific Year
        $this->assertEquals(800, $dataA['receivables_year']['total_outstanding'], 'Year A outstanding mismatch');
        $this->assertEquals(2000, $dataB['receivables_year']['total_outstanding'], 'Year B outstanding mismatch');

        // 3. Without year, receivables_year should be null
        $this->assertNull($dataAll['receivables_year']);
    }
}
