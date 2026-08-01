<?php

namespace Tests\Feature\Dashboard;

use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardQueryGuardTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuard(): object
    {
        return new class {
            use HasDashboardQueries;

            public function enrollment(array $filters)
            {
                return $this->enrollmentQuery($filters);
            }

            public function attendance(array $filters)
            {
                return $this->attendanceQuery($filters);
            }

            public function invoice(array $filters)
            {
                return $this->invoiceQuery($filters);
            }
        };
    }

    public function test_dashboard_queries_require_year_and_term_by_default(): void
    {
        $guard = $this->makeGuard();

        $this->expectException(\InvalidArgumentException::class);
        $guard->enrollment([]);
    }

    public function test_attendance_query_requires_term_id(): void
    {
        $guard = $this->makeGuard();

        $this->expectException(\InvalidArgumentException::class);
        $guard->attendance(['academicYearId' => 1]);
    }

    public function test_dashboard_queries_allow_all_years_flag(): void
    {
        $guard = $this->makeGuard();

        $guard->enrollment(['allYears' => true]);
        $guard->attendance(['allYears' => true]);
        $guard->invoice(['allYears' => true]);

        $this->addToAssertionCount(1);
    }
}
