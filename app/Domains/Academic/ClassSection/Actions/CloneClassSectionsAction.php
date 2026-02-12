<?php

namespace App\Domains\Academic\ClassSection\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Events\StructureChanged;
use App\Domains\Academic\Grade\Models\Grade;
use App\Infrastructure\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class CloneClassSectionsAction
{
    public function __construct(
        protected ClassSectionLookupService $lookupService,
        protected AcademicWriteGuard $writeGuard
    ) {
    }

    public function execute(int $sourceYearId, int $targetYearId): int
    {
        $sourceYear = AcademicYear::findOrFail($sourceYearId);
        $targetYear = AcademicYear::findOrFail($targetYearId);

        // Write guard applies to target year only (source is read-only).
        $this->writeGuard->assertYearNotClosed($targetYear->id);

        return DB::transaction(function () use ($sourceYear, $targetYear) {
            $createdCount = 0;
            $affectedGrades = [];

            foreach ($sourceYear->sections as $section) {
                if (!Grade::whereKey($section->grade_id)->exists()) {
                    throw BusinessRuleException::make('لا يمكن نسخ شعبة لصف غير موجود.');
                }

                // Check if section already exists in target year
                $exists = ClassSection::where('academic_year_id', $targetYear->id)
                    ->where('grade_id', $section->grade_id)
                    ->where('name', $section->name)
                    ->exists();

                if (!$exists) {
                    ClassSection::create([
                        'name' => $section->name,
                        'grade_id' => $section->grade_id,
                        'academic_year_id' => $targetYear->id,
                        'max_capacity' => $section->max_capacity,
                        'gender_type' => $section->gender_type,
                        'is_active' => true,
                    ]);
                    $createdCount++;
                    $affectedGrades[$section->grade_id] = true;
                }
            }

            // Invalidate cache for all affected grades in target year
            foreach (array_keys($affectedGrades) as $gradeId) {
                $this->lookupService->invalidateCache($gradeId, $targetYear->id);
            }

            if ($createdCount > 0) {
                StructureChanged::dispatch(
                    StructureChanged::TYPE_SECTION,
                    StructureChanged::ACTION_CREATED,
                    null
                );
            }

            return $createdCount;
        });
    }
}
