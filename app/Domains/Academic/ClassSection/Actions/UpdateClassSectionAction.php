<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Events\StructureChanged;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\BusinessRuleException;

class UpdateClassSectionAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService,
        protected AcademicWriteGuard $writeGuard
    ) {
    }

    public function execute(ClassSection $section, ClassSectionData $data): ClassSection
    {
        $this->writeGuard->assertYearNotClosed($data->academic_year_id);

        $exists = ClassSection::where('academic_year_id', $data->academic_year_id)
            ->where('grade_id', $data->grade_id)
            ->where('name', $data->name)
            ->where('id', '!=', $section->id)
            ->exists();

        if ($exists) {
            throw BusinessRuleException::dataConflict('هذه الشعبة موجودة بالفعل في نفس الصف والسنة.');
        }

        $oldGradeId = $section->grade_id;
        $oldYearId = $section->academic_year_id;

        $section->update($data->toArray());

        // Invalidate old cache
        $this->lookupService->invalidateCache($oldGradeId, $oldYearId);

        // Invalidate new cache if different
        if ($oldGradeId !== $data->grade_id || $oldYearId !== $data->academic_year_id) {
            $this->lookupService->invalidateCache($data->grade_id, $data->academic_year_id);
        }

        StructureChanged::dispatch(
            StructureChanged::TYPE_SECTION,
            StructureChanged::ACTION_UPDATED,
            $section
        );

        return $section;
    }
}
