<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Services\AcademicYearService;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;
use App\Domains\Academic\AcademicYear\Exceptions\DateOverlapException;
use App\Domains\Academic\AcademicYear\Exceptions\InvalidDateRangeException;
use App\Domains\Academic\AcademicYear\Exceptions\InsufficientTermsException;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use App\Domains\Academic\AcademicYear\Actions\CreateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\UpdateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\ActivateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\CloseAcademicYearAction;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_create_academic_year_via_action()
    {
        $data = new AcademicYearData(
            name: '2025-2026',
            start_date: Carbon::parse('2025-09-01'),
            end_date: Carbon::parse('2026-06-30'),
            status: AcademicYearStatus::Pending,
            terms: [
                ['name' => 'Term 1', 'start_date' => '2025-09-01', 'end_date' => '2026-01-15', 'order_index' => 1],
                ['name' => 'Term 2', 'start_date' => '2026-01-20', 'end_date' => '2026-06-30', 'order_index' => 2],
            ]
        );

        $year = app(CreateAcademicYearAction::class)->execute($data);

        $this->assertDatabaseHas('academic_years', ['name' => '2025-2026']);
        $this->assertCount(2, $year->terms);
    }

    /** @test */
    public function it_throws_exception_for_date_overlap()
    {
        // Create existing year
        AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        $this->expectException(DateOverlapException::class);

        $data = new AcademicYearData(
            name: '2025-2026',
            start_date: Carbon::parse('2025-01-01'), // Overlaps
            end_date: Carbon::parse('2025-12-31'),
            status: AcademicYearStatus::Pending,
            terms: [['name' => 'T1', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'order_index' => 1]]
        );

        app(CreateAcademicYearAction::class)->execute($data);
    }

    /** @test */
    public function it_throws_exception_for_invalid_date_range()
    {
        $this->expectException(InvalidDateRangeException::class);

        $data = new AcademicYearData(
            name: '2025-2026',
            start_date: Carbon::parse('2025-06-30'),
            end_date: Carbon::parse('2025-01-01'), // End before start
            status: AcademicYearStatus::Pending,
            terms: []
        );

        app(CreateAcademicYearAction::class)->execute($data);
    }

    /** @test */
    public function it_throws_exception_for_insufficient_terms()
    {
        $this->expectException(InsufficientTermsException::class);

        $data = new AcademicYearData(
            name: '2025-2026',
            start_date: Carbon::parse('2025-09-01'),
            end_date: Carbon::parse('2026-06-30'),
            status: AcademicYearStatus::Active,
            terms: [['name' => 'One Term', 'start_date' => '2025-09-01', 'end_date' => '2026-06-30', 'order_index' => 1]] // Only 1 term
        );

        app(CreateAcademicYearAction::class)->execute($data);
    }

    /** @test */
    public function it_activates_year_and_closes_others_via_action()
    {
        // Old Active
        $oldYear = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        // New Pending
        $newYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => AcademicYearStatus::Pending,
        ]);
        // Add terms manually as factory might not be set up
        Term::create(['academic_year_id' => $newYear->id, 'name' => 'T1', 'start_date' => '2024-09-01', 'end_date' => '2025-01-01', 'status' => 'pending']);
        Term::create(['academic_year_id' => $newYear->id, 'name' => 'T2', 'start_date' => '2025-02-01', 'end_date' => '2025-06-30', 'status' => 'pending']);

        app(ActivateAcademicYearAction::class)->execute($newYear);

        $this->assertEquals(AcademicYearStatus::Closed, $oldYear->fresh()->status);
        $this->assertEquals(AcademicYearStatus::Active, $newYear->fresh()->status);
    }

    /** @test */
    public function it_clears_cache_via_observer()
    {
        // Populate cache
        Cache::rememberForever(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::CACHE_KEY_LIST, fn() => 'cached_list');
        $this->assertEquals('cached_list', Cache::get(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::CACHE_KEY_LIST));

        // Trigger Observer via creation
        AcademicYear::create([
            'name' => '2028-2029',
            'start_date' => '2028-01-01',
            'end_date' => '2028-12-31',
            'status' => AcademicYearStatus::Pending,
        ]);

        // Assert cache is gone
        $this->assertNull(Cache::get(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::CACHE_KEY_LIST));
    }

    /** @test */
    public function it_prevents_deletion_of_active_year_via_action()
    {
        $activeYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        $this->expectException(YearNotDeletableException::class);

        app(DeleteAcademicYearAction::class)->execute($activeYear->id);
    }

    /** @test */
    public function it_can_close_active_year_via_action()
    {
        $activeYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        app(CloseAcademicYearAction::class)->execute($activeYear);

        $this->assertEquals(AcademicYearStatus::Closed, $activeYear->fresh()->status);
    }
    /** @test */
    public function it_can_delete_pending_year_with_no_students()
    {
        // Create a pending year
        $pendingYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => AcademicYearStatus::Pending,
        ]);

        // Assert it exists
        $this->assertDatabaseHas('academic_years', ['id' => $pendingYear->id]);

        // Delete via action
        app(DeleteAcademicYearAction::class)->execute($pendingYear->id);

        // Assert it is deleted
        $this->assertDatabaseMissing('academic_years', ['id' => $pendingYear->id]);
    }

    /** @test */
    public function it_blocks_deleting_pending_year_with_enrollments()
    {
        $year = AcademicYear::factory()->create(['status' => AcademicYearStatus::Pending]);
        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);
        $student = Student::factory()->create([
            'current_class_section_id' => $section->id,
            'current_grade_id' => $grade->id,
        ]);

        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'class_section_id' => $section->id,
        ]);

        $this->expectException(YearNotDeletableException::class);

        app(DeleteAcademicYearAction::class)->execute($year->id);
    }

    /** @test */
    public function it_keeps_enrollments_count_after_student_moves_years()
    {
        $year1 = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active]);
        $year2 = AcademicYear::factory()->create(['status' => AcademicYearStatus::Pending]);

        $grade1 = Grade::factory()->create();
        $grade2 = Grade::factory()->create();

        $section1 = ClassSection::factory()->create([
            'academic_year_id' => $year1->id,
            'grade_id' => $grade1->id,
        ]);
        $section2 = ClassSection::factory()->create([
            'academic_year_id' => $year2->id,
            'grade_id' => $grade2->id,
        ]);

        $student = Student::factory()->create([
            'current_class_section_id' => $section1->id,
            'current_grade_id' => $grade1->id,
        ]);

        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year1->id,
            'grade_id' => $grade1->id,
            'class_section_id' => $section1->id,
        ]);

        $year1->loadCount(['students', 'enrollments']);
        $this->assertSame(1, $year1->students_count);
        $this->assertSame(1, $year1->enrollments_count);

        $student->update([
            'current_class_section_id' => $section2->id,
            'current_grade_id' => $grade2->id,
        ]);

        $year1->refresh()->loadCount(['students', 'enrollments']);
        $this->assertSame(0, $year1->students_count);
        $this->assertSame(1, $year1->enrollments_count);
    }

    /** @test */
    public function it_can_activate_pending_year_when_no_active_year_exists()
    {
        // Ensure no active years exist
        AcademicYear::query()->update(['status' => AcademicYearStatus::Closed]);

        // Create a pending year
        $pendingYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => AcademicYearStatus::Pending,
        ]);
        Term::create(['academic_year_id' => $pendingYear->id, 'name' => 'T1', 'start_date' => '2025-01-01', 'end_date' => '2025-06-01', 'status' => 'pending']);
        Term::create(['academic_year_id' => $pendingYear->id, 'name' => 'T2', 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'status' => 'pending']);

        // Activate via action
        app(ActivateAcademicYearAction::class)->execute($pendingYear);

        // Assert it becomes active
        $this->assertEquals(AcademicYearStatus::Active, $pendingYear->fresh()->status);
    }

    /** @test */
    public function it_activates_pending_year_even_when_active_year_cache_is_stale()
    {
        Cache::flush();

        $yearA = AcademicYear::create([
            'name' => '2022-2023',
            'start_date' => '2022-09-01',
            'end_date' => '2023-06-30',
            'status' => AcademicYearStatus::Pending,
        ]);

        $yearB = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        $termB1 = Term::create([
            'academic_year_id' => $yearB->id,
            'name' => 'B-T1',
            'start_date' => '2023-09-01',
            'end_date' => '2024-01-01',
            'order_index' => 1,
            'status' => TermStatus::Pending,
        ]);
        $termB1->update(['status' => TermStatus::Completed]);

        $termB2 = Term::create([
            'academic_year_id' => $yearB->id,
            'name' => 'B-T2',
            'start_date' => '2024-01-15',
            'end_date' => '2024-06-30',
            'order_index' => 2,
            'status' => TermStatus::Pending,
        ]);
        $termB2->update(['status' => TermStatus::Completed]);

        $yearC = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => AcademicYearStatus::Pending,
        ]);
        Term::create([
            'academic_year_id' => $yearC->id,
            'name' => 'C-T1',
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-01',
            'order_index' => 1,
            'status' => TermStatus::Pending,
        ]);
        Term::create([
            'academic_year_id' => $yearC->id,
            'name' => 'C-T2',
            'start_date' => '2025-01-15',
            'end_date' => '2025-06-30',
            'order_index' => 2,
            'status' => TermStatus::Pending,
        ]);

        Cache::put(AcademicContextService::CACHE_KEY_YEAR, $yearA, 60 * 60 * 24);
        $this->assertEquals($yearA->id, Cache::get(AcademicContextService::CACHE_KEY_YEAR)->id);

        app(ActivateAcademicYearAction::class)->execute($yearC);

        $this->assertEquals(AcademicYearStatus::Closed, $yearB->fresh()->status);
        $this->assertEquals(AcademicYearStatus::Active, $yearC->fresh()->status);
        $this->assertEquals(1, AcademicYear::where('status', AcademicYearStatus::Active)->count());
    }
}
