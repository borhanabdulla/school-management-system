<?php

namespace Tests\Unit\Domains\Academic;

use Tests\TestCase;
use App\Domains\Academic\Data\ReadinessItem;
use App\Domains\Academic\Data\Enums\ReadinessSeverity;
use App\Domains\Academic\Services\ReadinessService;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Results\Enums\ResultDecision;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Services\StudentEnrollmentQueryService;

/**
 * Unit tests for ReadinessService
 *
 * Tests the readiness check items for academic year closing readiness
 */
class ReadinessServiceTest extends TestCase
{
    protected ReadinessService $service;
    protected StudentEnrollmentQueryService $studentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentService = $this->app->make(StudentEnrollmentQueryService::class);
        $this->service = new ReadinessService($this->studentService);
    }

    /** @test */
    public function it_returns_blocking_item_for_incomplete_terms()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active->value, // Not completed
        ]);

        $completedTerm = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending->value,
        ]);
        $completedTerm->update(['status' => TermStatus::Completed->value]);

        // Act
        $items = $this->service->getReadinessItems($year);

        // Assert
        $termsItem = $items->firstWhere('key', ReadinessService::KEY_TERMS_NOT_COMPLETED);
        $this->assertNotNull($termsItem);
        $this->assertTrue($termsItem->isBlocking());
        $this->assertEquals(1, $termsItem->count);
    }

    /** @test */
    public function it_returns_zero_count_when_all_terms_completed()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        $completedTerm1 = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending->value,
        ]);
        $completedTerm1->update(['status' => TermStatus::Completed->value]);

        $completedTerm2 = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending->value,
        ]);
        $completedTerm2->update(['status' => TermStatus::Completed->value]);

        // Act
        $items = $this->service->getReadinessItems($year);
        $termsItem = $items->firstWhere('key', ReadinessService::KEY_TERMS_NOT_COMPLETED);

        // Assert
        $this->assertNotNull($termsItem);
        $this->assertEquals(0, $termsItem->count);
        $this->assertFalse($termsItem->hasIssues());
    }

    /** @test */
    public function it_returns_blocking_item_for_pending_annual_results()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        AnnualResult::factory()->create([
            'academic_year_id' => $year->id,
            'decision' => 'pending',
        ]);

        AnnualResult::factory()->create([
            'academic_year_id' => $year->id,
            'decision' => ResultDecision::Pass->value,
        ]);

        // Act
        $items = $this->service->getReadinessItems($year);

        // Assert
        $resultsItem = $items->firstWhere('key', ReadinessService::KEY_ANNUAL_RESULTS_PENDING);
        $this->assertNotNull($resultsItem);
        $this->assertTrue($resultsItem->isBlocking());
        $this->assertEquals(1, $resultsItem->count);
    }

    /** @test */
    public function it_returns_blocking_item_for_incomplete_promotions()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        // Note: This test verifies the service structure
        // The actual count depends on StudentEnrollmentQueryService

        // Act
        $items = $this->service->getReadinessItems($year);

        // Assert
        $promotionItem = $items->firstWhere('key', ReadinessService::KEY_PROMOTION_INCOMPLETE);
        $this->assertNotNull($promotionItem);
        $this->assertTrue($promotionItem->isBlocking());
    }

    /** @test */
    public function it_returns_warning_item_for_missing_attendance()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active->value,
        ]);

        // Act
        $items = $this->service->getReadinessItems($year);

        // Assert
        $attendanceItem = $items->firstWhere('key', ReadinessService::KEY_ATTENDANCE_MISSING);
        $this->assertNotNull($attendanceItem);
        $this->assertTrue($attendanceItem->isWarning());
    }

    /** @test */
    public function it_returns_warning_item_for_missing_marks()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        $completedTerm = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending->value,
        ]);
        $completedTerm->update(['status' => TermStatus::Completed->value]);

        // Act
        $items = $this->service->getReadinessItems($year);

        // Assert
        $marksItem = $items->firstWhere('key', ReadinessService::KEY_MARKS_MISSING);
        $this->assertNotNull($marksItem);
        $this->assertTrue($marksItem->isWarning());
    }

    /** @test */
    public function it_can_close_returns_true_when_no_blocking_issues()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        // Complete all terms
        $completedTerm = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending->value,
        ]);
        $completedTerm->update(['status' => TermStatus::Completed->value]);

        // Complete all annual results
        AnnualResult::factory()->create([
            'academic_year_id' => $year->id,
            'decision' => ResultDecision::Pass->value,
        ]);

        // Act
        $result = $this->service->canClose($year);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_close_returns_false_when_blocking_issues_exist()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'status' => 'active',
        ]);

        // Incomplete term
        Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active->value,
        ]);

        // Act
        $result = $this->service->canClose($year);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_correct_summary()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'name' => '2082/2083',
            'status' => 'active',
        ]);

        // Act
        $summary = $this->service->getSummary($year);

        // Assert
        $this->assertArrayHasKey('year_id', $summary);
        $this->assertArrayHasKey('year_name', $summary);
        $this->assertArrayHasKey('can_close', $summary);
        $this->assertArrayHasKey('blocking_count', $summary);
        $this->assertArrayHasKey('warning_count', $summary);
        $this->assertArrayHasKey('items', $summary);
        $this->assertEquals($year->id, $summary['year_id']);
        $this->assertEquals('2082/2083', $summary['year_name']);
    }

    /** @test */
    public function it_has_correct_route_params()
    {
        // Arrange
        $year = AcademicYear::factory()->create([
            'id' => 42,
            'status' => 'active',
        ]);

        // Act
        $items = $this->service->getReadinessItems($year);

        // Assert
        $termsItem = $items->firstWhere('key', ReadinessService::KEY_TERMS_NOT_COMPLETED);
        $this->assertNotNull($termsItem);
        $this->assertEquals(['year_id' => 42], $termsItem->routeParams);
    }

    /** @test */
    public function readiness_item_has_correct_badge_class()
    {
        // Test blocking item
        $blocking = ReadinessItem::blocking(
            'test',
            'Label',
            'Message',
            5
        );
        $this->assertEquals('badge-danger', $blocking->getBadgeClass());
        $this->assertEquals('heroicon-o-x-circle', $blocking->getIcon());

        // Test warning item
        $warning = ReadinessItem::warning(
            'test',
            'Label',
            'Message',
            3
        );
        $this->assertEquals('badge-warning', $warning->getBadgeClass());
        $this->assertEquals('heroicon-o-exclamation-triangle', $warning->getIcon());
    }

    /** @test */
    public function readiness_item_to_array_works()
    {
        // Arrange
        $item = ReadinessItem::blocking(
            'test_key',
            'Test Label',
            'Test Message',
            10,
            'test.route',
            ['id' => 1]
        );

        // Act
        $array = $item->toArray();

        // Assert
        $this->assertArrayHasKey('key', $array);
        $this->assertArrayHasKey('severity', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('count', $array);
        $this->assertArrayHasKey('route', $array);
        $this->assertArrayHasKey('route_params', $array);
        $this->assertArrayHasKey('is_blocking', $array);
        $this->assertArrayHasKey('is_warning', $array);
        $this->assertArrayHasKey('has_issues', $array);

        $this->assertEquals('test_key', $array['key']);
        $this->assertEquals('blocking', $array['severity']);
        $this->assertEquals(10, $array['count']);
        $this->assertTrue($array['is_blocking']);
        $this->assertFalse($array['is_warning']);
        $this->assertTrue($array['has_issues']);
    }

    /** @test */
    public function readiness_severity_has_correct_values()
    {
        $blocking = ReadinessSeverity::Blocking;
        $warning = ReadinessSeverity::Warning;

        $this->assertEquals('blocking', $blocking->value);
        $this->assertEquals('warning', $warning->value);
        $this->assertTrue($blocking->blocksClosure());
        $this->assertFalse($warning->blocksClosure());
    }
}
