<?php

namespace App\Domains\Academic\Subject\Services;

use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Grade\Models\Grade;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class SubjectLookupService
{
    public const CACHE_KEY_SUBJECTS_LIST = 'subjects_list';
    public const CACHE_KEY_CURRICULUM_BY_GRADE = 'curriculum_grade_';

    public function __construct(
        protected AcademicContextService $context
    ) {
    }

    /**
     * الحصول على قائمة المواد (للمكتبة)
     * 
     * @return Collection
     */
    public function getSubjectsList(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY_SUBJECTS_LIST, function () {
            return Subject::orderBy('name')->get();
        });
    }

    /**
     * الحصول على منهج صف معين (Curriculum)
     * 
     * @param int $gradeId
     * @return Collection
     */
    public function getCurriculumByGrade(int $gradeId): Collection
    {
        // TODO: Future Enhancement - Use Context to filter by Active Year if GradeSubject becomes YearBased
        // Currently, GradeSubject is static per Grade, so we don't strictly need the year yet,
        // but we prepare the structure for it.

        return Cache::rememberForever(self::CACHE_KEY_CURRICULUM_BY_GRADE . $gradeId, function () use ($gradeId) {
            return Grade::with([
                'subjects' => function ($q) {
                    $q->orderBy('grade_subjects.term_type')->orderBy('name');
                }
            ])->find($gradeId)?->subjects ?? new Collection();
        });
    }

    /**
     * إبطال كاش قائمة المواد
     */
    public function invalidateSubjectsListCache(): void
    {
        Cache::forget(self::CACHE_KEY_SUBJECTS_LIST);
    }

    /**
     * إبطال كاش منهج صف معين
     */
    public function invalidateCurriculumCache(int $gradeId): void
    {
        Cache::forget(self::CACHE_KEY_CURRICULUM_BY_GRADE . $gradeId);
    }
}
