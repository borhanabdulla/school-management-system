<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Academic;

use Tests\TestCase;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\ResourceNotFoundException;
use App\Infrastructure\Exceptions\InvalidOperationException;

class AcademicWriteGuardTest extends TestCase
{
    protected AcademicWriteGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = app(AcademicWriteGuard::class);
    }

    /** @test */
    public function it_allows_writing_when_year_is_active(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active
        ]);

        $this->assertTrue($this->guard->isWritable($year->id));
    }

    /** @test */
    public function it_allows_writing_when_year_is_pending(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Pending
        ]);

        $this->assertTrue($this->guard->isWritable($year->id));
    }

    /** @test */
    public function it_blocks_writing_when_year_is_closed(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Closed
        ]);

        $this->assertFalse($this->guard->isWritable($year->id));

        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('مغلقة أو مؤرشفة ولا يمكن التعديل');

        $this->guard->assertYearNotClosed($year->id);
    }

    /** @test */
    public function it_throws_not_found_when_year_does_not_exist(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->guard->assertYearNotClosed(99999);
    }

    /** @test */
    public function it_allows_writing_when_term_is_pending(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active
        ]);

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending
        ]);

        $this->assertTrue($this->guard->isWritable($year->id, $term->id));
    }

    /** @test */
    public function it_allows_writing_when_term_is_active(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active
        ]);

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active
        ]);

        $this->assertTrue($this->guard->isWritable($year->id, $term->id));
    }

    /** @test */
    public function it_blocks_writing_when_term_is_completed(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active
        ]);

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending
        ]);
        $term->update(['status' => TermStatus::Completed]);

        $this->assertFalse($this->guard->isWritable($year->id, $term->id));

        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('مكتمل ولا يمكن التعديل');

        $this->guard->assertTermNotCompleted($term->id);
    }

    /** @test */
    public function it_blocks_writing_when_year_is_closed_even_if_term_is_active(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active
        ]);

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Active
        ]);
        $year->update(['status' => AcademicYearStatus::Closed]);

        $this->assertFalse($this->guard->isWritable($year->id, $term->id));

        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('مغلقة أو مؤرشفة ولا يمكن التعديل');

        $this->guard->assertTermNotCompleted($term->id);
    }

    /** @test */
    public function it_throws_not_found_when_term_does_not_exist(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->guard->assertTermNotCompleted(99999);
    }

    /** @test */
    public function get_blocked_message_returns_correct_message_for_closed_year(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Closed,
            'name' => '2024-2025'
        ]);

        $message = $this->guard->getBlockedMessage($year->id);

        $this->assertStringContainsString('2024-2025', $message);
        $this->assertStringContainsString('مغلقة', $message);
    }

    /** @test */
    public function get_blocked_message_returns_correct_message_for_completed_term(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
            'name' => '2024-2025'
        ]);

        $term = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending,
            'name' => 'الفصل الأول'
        ]);
        $term->update(['status' => TermStatus::Completed]);

        $message = $this->guard->getBlockedMessage($year->id, $term->id);

        $this->assertStringContainsString('الفصل الأول', $message);
        $this->assertStringContainsString('مكتمل', $message);
    }
}
