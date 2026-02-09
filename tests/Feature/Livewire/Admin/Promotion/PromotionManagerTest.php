<?php

namespace Tests\Feature\Livewire\Admin\Promotion;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Results\Enums\ResultDecision;
use App\Domains\Academic\Student\Models\Student;
use App\Livewire\Admin\Promotion\PromotionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromotionManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_promotion_manager(): void
    {
        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();

        Livewire::test(PromotionManager::class)
            ->assertStatus(200);
    }

    public function test_it_filters_students_for_promotion(): void
    {
        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $student = Student::factory()->create([
            'current_grade_id' => $grade->id,
            'current_class_section_id' => $section->id,
        ]);

        // Create Annual Result for the student to make them eligible
        AnnualResult::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'decision' => ResultDecision::Pass,
        ]);

        Livewire::withQueryParams(['selectedYearId' => $year->id, 'selectedGradeId' => $grade->id])
            ->test(PromotionManager::class)
            ->assertSee($student->first_name_ar);
    }

    public function test_it_can_promote_student(): void
    {
        $year = AcademicYear::factory()->active()->create();
        school()->invalidateYear();

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $student = Student::factory()->create([
            'current_grade_id' => $grade->id,
            'current_class_section_id' => $section->id,
        ]);

        AnnualResult::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'decision' => ResultDecision::Pass,
        ]);

        // Ensure next year exists
        $nextYear = AcademicYear::factory()->create([
            'start_date' => $year->end_date->addDay(),
            'end_date' => $year->end_date->addYear(),
            'status' => 'pending',
        ]);

        // Ensure next grade exists (assuming simple promotion logic or manual mapping in service)
        // For this test, we just check if the action is called.
        // However, PromotionService logic might be complex.
        // Let's just verify the component calls the service.
        // But integration test is better.

        // We need a next grade for promotion to work usually.
        $nextGrade = Grade::factory()->create(['educational_stage_id' => $grade->educational_stage_id]);

        // Mocking PromotionService might be easier if logic is complex,
        // but let's try real integration if possible.
        // Actually, PromotionService::promote logic depends on Grade/Section availability.

        // Let's just assert the method exists and runs without error for now,
        // assuming PromotionService is tested separately.
        // Or we can mock the service.

        $this->mock(\App\Domains\Academic\Promotion\Services\PromotionService::class, function ($mock) use ($student, $year) {
            $mock->shouldReceive('getNextAcademicYear')->andReturn(
                AcademicYear::factory()->create(['start_date' => $year->end_date->addDay()])
            );
            $mock->shouldReceive('promote')->once();
            $mock->shouldReceive('validateSchoolReadyForPromotion')->andReturn(['ready' => true]);
            $mock->shouldReceive('getStatistics')->andReturn([]);
            $mock->shouldReceive('getNewYearComposition')->andReturn([]);
        });

        Livewire::withQueryParams(['selectedYearId' => $year->id])
            ->test(PromotionManager::class)
            ->call('promoteStudent', $student->id)
            ->assertDispatched('notify');
    }
}
