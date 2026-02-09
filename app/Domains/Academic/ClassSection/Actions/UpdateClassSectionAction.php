<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Events\StructureChanged;

class UpdateClassSectionAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService
    ) {
    }

    public function execute(ClassSection $section, ClassSectionData $data): ClassSection
    {
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
