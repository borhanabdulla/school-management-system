<?php

namespace App\Domains\Academic\Grade\Services;

use App\Domains\Academic\Grade\Models\Grade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

/**
 * GradeLookupService - خدمة القراءة فقط للصفوف الدراسية.
 *
 * تحتوي على عمليات الاستعلام والبحث مع الكاش.
 * لا تحتوي على أي عمليات كتابة.
 */
class GradeLookupService
{
    public const CACHE_KEY_GRADES_LIST = 'grades_list';
    public const CACHE_KEY_GRADES_BY_STAGE = 'grades_by_stage_';
    public const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all grades, cached.
     */
    public function getGradesList(): Collection
    {
        return Cache::remember(self::CACHE_KEY_GRADES_LIST, self::CACHE_TTL, function () {
            return Grade::with('stage')->orderBy('level_order')->get();
        });
    }

    /**
     * Get grades by stage, cached.
     */
    public function getGradesByStage(int $stageId): Collection
    {
        return Cache::remember(self::CACHE_KEY_GRADES_BY_STAGE . $stageId, self::CACHE_TTL, function () use ($stageId) {
            return Grade::where('educational_stage_id', $stageId)
                ->orderBy('level_order')
                ->get();
        });
    }

    /**
     * Get the next level order for a new grade in a stage.
     * Encapsulates shadow code from StructureManager.
     */
    public function getNextLevelOrder(int $stageId): int
    {
        $max = Grade::where('educational_stage_id', $stageId)->max('level_order');
        return $max ? $max + 1 : 1;
    }

    /**
     * Get grades available for selection as "Next Grade".
     * Excludes the specified grade ID to prevent self-referencing.
     * Encapsulates shadow code from StructureManager.
     */
    public function getGradesForNextSelection(?int $excludeId = null): Collection
    {
        return Grade::with('stage')
            ->join('educational_stages', 'grades.educational_stage_id', '=', 'educational_stages.id')
            ->orderBy('educational_stages.rank')
            ->orderBy('grades.level_order')
            ->select('grades.*')
            ->when($excludeId, fn($q) => $q->where('grades.id', '!=', $excludeId))
            ->get();
    }

    /**
     * Get grades with full statistics (Sections, Subjects, Students).
     * Context-aware: Uses active academic year.
     * Cached with Redis Aggregation.
     */
    public function getGradesWithStats(): Collection
    {
        $yearId = \App\Infrastructure\Context\AcademicContextService::getInstance()->activeYearId();

        // If no active year, return basic list or empty
        if (!$yearId) {
            return $this->getGradesList();
        }

        $key = 'academic_directory_stats_' . $yearId;

        return Cache::remember($key, self::CACHE_TTL, function () use ($yearId) {
            return Grade::with('stage')
                ->withCount([
                    'sections' => fn($q) => $q->where('academic_year_id', $yearId),
                    'subjects',
                    // Count all students enrolled in this grade for the active year
                    'students as students_count' => fn($q) => $q->whereHas('currentClassSection', fn($sq) => $sq->where('academic_year_id', $yearId))
                ])
                // Also eager load sections for the "Sections Chips" in UI
                // Eager load sections for the "Sections Chips" in UI
                ->with(['sections' => fn($q) => $q->where('academic_year_id', $yearId)->select('id', 'name', 'grade_id')])
                ->orderBy('level_order')
                ->get()
                ->map(function ($grade) use ($yearId) {
                    // Add teachers count (approximate via sections -> homeroom or subjects)
                    // For now, let's keep it simple or use a separate query if needed.
                    // The UI requested teachers_count.
                    // Let's assume teachers are linked via CourseOfferings in this year.
                    $grade->teachers_count = \App\Domains\Academic\CourseOffering\Models\CourseOffering::where('academic_year_id', $yearId)
                        ->whereHas('classSection', fn($q) => $q->where('grade_id', $grade->id))
                        ->distinct('teacher_id')
                        ->count('teacher_id');

                    return $grade;
                });
        });
    }

    /**
     * Invalidate cache for grades.
     * Call this from Actions after any write operation.
     */
    public static function invalidateCache(?int $stageId = null): void
    {
        Cache::forget(self::CACHE_KEY_GRADES_LIST);
        if ($stageId) {
            Cache::forget(self::CACHE_KEY_GRADES_BY_STAGE . $stageId);
        }

        // Invalidate directory stats for active year (and potentially others if we knew them)
        // Ideally, we should pass yearId to invalidate, but for now we invalidate current.
        $yearId = \App\Infrastructure\Context\AcademicContextService::getInstance()->activeYearId();
        if ($yearId) {
            Cache::forget('academic_directory_stats_' . $yearId);
        }
    }
    /**
     * Get grades filtered by Academic Year (Cascading Backend Logic)
     */
    public function getGradesByAcademicYear(?int $academicYearId = null)
    {
        $yearId = $academicYearId ?? \App\Infrastructure\Context\AcademicContextService::getInstance()->activeYearId();

        if (!$yearId) {
            return collect([]);
        }

        return Cache::remember("grades_by_year_{$yearId}", 3600, function () use ($yearId) {
            return Grade::whereHas('sections', function ($q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            })
                ->distinct()
                ->with('stage:id,name')
                ->orderBy('level_order')
                ->select('id', 'name', 'educational_stage_id', 'level_order')
                ->get();
        });
    }
}
