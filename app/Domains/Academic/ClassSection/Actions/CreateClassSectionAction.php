<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Events\StructureChanged;

class CreateClassSectionAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService
    ) {
    }

    public function execute(ClassSectionData $data): ClassSection
    {
        $section = ClassSection::create($data->toArray());

        $this->lookupService->invalidateCache($data->grade_id, $data->academic_year_id);

        StructureChanged::dispatch(
            StructureChanged::TYPE_SECTION,
            StructureChanged::ACTION_CREATED,
            $section
        );

        return $section;
    }
}
