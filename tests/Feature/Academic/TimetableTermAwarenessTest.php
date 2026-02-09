<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\Employee\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Academic\TimetableBuilder;
use App\Infrastructure\Context\AcademicContextService;

class TimetableTermAwarenessTest extends TestCase
{
    use RefreshDatabase;

    private $year;
    private $term1;
    private $term2;
    private $section;
    private $slot;
    private $teacher;
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Academic Context
        $this->year = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active]);
        $this->term1 = Term::factory()->create(['academic_year_id' => $this->year->id, 'name' => 'Term 1', 'status' => TermStatus::Active]);
        $this->term2 = Term::factory()->create(['academic_year_id' => $this->year->id, 'name' => 'Term 2', 'status' => TermStatus::Pending]); // Non-active

        // 2. Setup Basic Entities
        $grade = Grade::factory()->create(); // Grade is global, doesn't need academic_year
        $this->section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $this->year->id // Ensure section is linked to the year
        ]);
        $this->subject = Subject::factory()->create();
        $this->teacher = Teacher::factory()->create();

        // 3. Setup Timetable Template & Slot
        $template = TimetableTemplate::factory()->create(['academic_year_id' => $this->year->id]);
        $this->slot = TimeSlot::factory()->create(['template_id' => $template->id, 'day_of_week' => 0]);

        // Link template to grade
        DB::table('grade_timetable_template')->insert([
            'grade_id' => $grade->id,
            'template_id' => $template->id,
            'academic_year_id' => $this->year->id
        ]);
    }

    /** @test */
    public function it_can_store_different_sessions_for_different_terms_in_same_slot()
    {
        // Add Session in Term 1
        $t1 = Timetable::create([
            'class_section_id' => $this->section->id,
            'time_slot_id' => $this->slot->id,
            'term_id' => $this->term1->id,
        ]);

        // Add Session in Term 2 (Same Slot) -> Should Succeed
        $t2 = Timetable::create([
            'class_section_id' => $this->section->id,
            'time_slot_id' => $this->slot->id,
            'term_id' => $this->term2->id,
        ]);

        $this->assertDatabaseCount('timetables', 2);
        $this->assertEquals($this->term1->id, $t1->term_id);
        $this->assertEquals($this->term2->id, $t2->term_id);
    }

    /** @test */
    public function it_prevents_duplicate_sessions_in_same_term_and_slot()
    {
        Timetable::create([
            'class_section_id' => $this->section->id,
            'time_slot_id' => $this->slot->id,
            'term_id' => $this->term1->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to add duplicate in same term
        Timetable::create([
            'class_section_id' => $this->section->id,
            'time_slot_id' => $this->slot->id,
            'term_id' => $this->term1->id,
        ]);
    }

    /** @test */
    public function scope_for_term_correctly_filters_sessions()
    {
        $t1 = Timetable::create(['class_section_id' => $this->section->id, 'time_slot_id' => $this->slot->id, 'term_id' => $this->term1->id]);
        // Create another slot for term 2 to avoid unique constraint if we use same slot
        $slot2 = TimeSlot::factory()->create(['template_id' => $this->slot->template_id, 'day_of_week' => 1]);
        $t2 = Timetable::create(['class_section_id' => $this->section->id, 'time_slot_id' => $slot2->id, 'term_id' => $this->term2->id]);

        $results1 = Timetable::forTerm($this->term1->id)->get();
        $results2 = Timetable::forTerm($this->term2->id)->get();

        $this->assertCount(1, $results1);
        $this->assertTrue($results1->where('id', $t1->id)->count() > 0);
        $this->assertFalse($results1->where('id', $t2->id)->count() > 0);

        $this->assertCount(1, $results2);
        $this->assertTrue($results2->where('id', $t2->id)->count() > 0);
    }

    /** @test */
    public function livewire_builder_restricts_modification_of_non_active_terms()
    {
        // Mock Context to set Term 1 as Active
        $contextMock = \Mockery::mock(AcademicContextService::class);
        $contextMock->shouldReceive('activeYear')->andReturn($this->year);
        $contextMock->shouldReceive('activeTerm')->andReturn($this->term1); // Term 1 is Active
        $this->app->instance(AcademicContextService::class, $contextMock);

        // Test attempting to save to Term 2 (Non-Active)
        // Should trigger isReadOnly check and dispatch error, OR abort if bypassed.
        // Since isReadOnly shares logic, it typically catches it first.
        Livewire::test(TimetableBuilder::class)
            ->set('selectedYearId', $this->year->id)
            ->set('selectedTermId', $this->term2->id) // Selected Term 2
            ->set('selectedGradeId', $this->section->grade_id)
            ->set('selectedSectionId', $this->section->id)
            ->set('selectedSlotId', $this->slot->id)
            ->set('selectedTeacherId', $this->teacher->id)
            ->set('selectedSubjectId', $this->subject->id)
            ->call('saveSession')
            ->assertDispatched('error'); // Expect UI error message
    }
}
