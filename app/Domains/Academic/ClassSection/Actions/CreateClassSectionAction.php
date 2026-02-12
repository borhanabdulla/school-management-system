<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Events\StructureChanged;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\BusinessRuleException;

class CreateClassSectionAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService,
        protected AcademicWriteGuard $writeGuard
    ) {
    }

    public function execute(ClassSectionData $data): ClassSection
    {
        $this->writeGuard->assertYearNotClosed($data->academic_year_id);

        $exists = ClassSection::where('academic_year_id', $data->academic_year_id)
            ->where('grade_id', $data->grade_id)
            ->where('name', $data->name)
            ->exists();

        if ($exists) {
            throw BusinessRuleException::dataConflict('هذه الشعبة موجودة بالفعل في نفس الصف والسنة.');
        }

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
