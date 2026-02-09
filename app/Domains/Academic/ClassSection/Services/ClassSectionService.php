<?php

namespace App\Domains\Academic\ClassSection\Services;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\UpdateClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\DeleteClassSectionAction;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class ClassSectionService
{
    public const CACHE_KEY_SECTIONS_BY_GRADE = 'sections_by_grade_';

    public function __construct(
        protected CreateClassSectionAction $createAction,
        protected UpdateClassSectionAction $updateAction,
        protected DeleteClassSectionAction $deleteAction,
    ) {
    }

    /**
     * Get sections by grade and year, cached.
     *
     * @param int $gradeId
     * @param int $yearId
     * @return Collection
     */
    public function getSectionsByGrade(int $gradeId, int $yearId): Collection
    {
        // Cache key includes both grade and year to ensure uniqueness
        $key = self::CACHE_KEY_SECTIONS_BY_GRADE . "{$gradeId}_{$yearId}";

        return Cache::rememberForever($key, function () use ($gradeId, $yearId) {
            return ClassSection::where('grade_id', $gradeId)
                ->where('academic_year_id', $yearId)
                ->orderBy('name')
                ->get();
        });
    }

    public function createClassSection(ClassSectionData $data): ClassSection
    {
        $section = $this->createAction->execute($data);
        $this->invalidateCache($data->grade_id, $data->academic_year_id);
        return $section;
    }

    public function updateClassSection(ClassSection $section, ClassSectionData $data): ClassSection
    {
        $oldGradeId = $section->grade_id;
        $oldYearId = $section->academic_year_id;

        $updatedSection = $this->updateAction->execute($section, $data);

        // Invalidate cache for the old grade/year (if changed) and the new one
        $this->invalidateCache($oldGradeId, $oldYearId);
        if ($oldGradeId !== $data->grade_id || $oldYearId !== $data->academic_year_id) {
            $this->invalidateCache($data->grade_id, $data->academic_year_id);
        }

        return $updatedSection;
    }

    public function deleteClassSection(ClassSection $section): void
    {
        $gradeId = $section->grade_id;
        $yearId = $section->academic_year_id;

        $this->deleteAction->execute($section);

        $this->invalidateCache($gradeId, $yearId);
    }

    protected function invalidateCache(int $gradeId, int $yearId): void
    {
        $key = self::CACHE_KEY_SECTIONS_BY_GRADE . "{$gradeId}_{$yearId}";
        Cache::forget($key);
    }
}