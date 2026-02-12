<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\DeleteClassSectionAction;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Grade\Models\Grade;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSectionLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_invalidates_flat_section_cache_after_create_and_delete(): void
    {
        $year = AcademicYear::factory()->active()->create();
        AcademicContextService::getInstance()->invalidate();

        $grade = Grade::factory()->create();

        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
            'name' => 'A',
        ]);

        $lookup = app(ClassSectionLookupService::class);

        $first = $lookup->getAllActiveSectionsFlat();
        $this->assertCount(1, $first);

        $createData = ClassSectionData::fromArray([
            'name' => 'B',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'max_capacity' => 30,
            'gender_type' => SectionGenderType::Mixed,
            'is_active' => true,
        ]);

        app(CreateClassSectionAction::class)->execute($createData);

        $second = $lookup->getAllActiveSectionsFlat();
        $this->assertCount(2, $second);

        app(DeleteClassSectionAction::class)->execute($section);

        $third = $lookup->getAllActiveSectionsFlat();
        $this->assertCount(1, $third);
    }
}
