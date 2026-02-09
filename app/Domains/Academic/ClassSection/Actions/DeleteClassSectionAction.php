<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Exceptions\SectionHasStudentsException;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Events\StructureChanged;

class DeleteClassSectionAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService
    ) {
    }

    public function execute(ClassSection $section): void
    {
        if ($section->students()->exists()) {
            throw new SectionHasStudentsException();
        }

        $gradeId = $section->grade_id;
        $yearId = $section->academic_year_id;

        $section->delete();

        $this->lookupService->invalidateCache($gradeId, $yearId);

        StructureChanged::dispatch(
            StructureChanged::TYPE_SECTION,
            StructureChanged::ACTION_DELETED,
            $section
        );
    }
}
