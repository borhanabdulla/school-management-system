<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\DeleteClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\UpdateClassSectionAction;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSectionWriteGuardTest extends TestCase
{
    use RefreshDatabase;

    public static function closedStatusesProvider(): array
    {
        return [
            'closed' => [AcademicYearStatus::Closed],
            'archived' => [AcademicYearStatus::Archived],
        ];
    }

    /**
     * @dataProvider closedStatusesProvider
     */
    public function test_it_blocks_creating_section_in_closed_or_archived_year(AcademicYearStatus $status): void
    {
        $year = AcademicYear::factory()->create(['status' => $status]);
        $grade = Grade::factory()->create();

        $data = ClassSectionData::fromArray([
            'name' => 'Section A',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'max_capacity' => 30,
            'gender_type' => SectionGenderType::Mixed,
            'is_active' => true,
        ]);

        $this->expectException(InvalidOperationException::class);

        app(CreateClassSectionAction::class)->execute($data);
    }

    /**
     * @dataProvider closedStatusesProvider
     */
    public function test_it_blocks_updating_section_in_closed_or_archived_year(AcademicYearStatus $status): void
    {
        $year = AcademicYear::factory()->active()->create();
        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $year->update(['status' => $status]);

        $data = ClassSectionData::fromArray([
            'name' => 'Updated',
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'max_capacity' => 30,
            'gender_type' => SectionGenderType::Mixed,
            'is_active' => true,
        ]);

        $this->expectException(InvalidOperationException::class);

        app(UpdateClassSectionAction::class)->execute($section, $data);
    }

    /**
     * @dataProvider closedStatusesProvider
     */
    public function test_it_blocks_deleting_section_in_closed_or_archived_year(AcademicYearStatus $status): void
    {
        $year = AcademicYear::factory()->active()->create();
        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'academic_year_id' => $year->id,
            'grade_id' => $grade->id,
        ]);

        $year->update(['status' => $status]);

        $this->expectException(InvalidOperationException::class);

        app(DeleteClassSectionAction::class)->execute($section);
    }
}
