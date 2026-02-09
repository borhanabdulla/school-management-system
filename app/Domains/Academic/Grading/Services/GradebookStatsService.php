<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\Grading\Data\ClassStatsData;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * GradebookStatsService - خدمة إحصائيات دفتر العلامات
 * 
 * تستخدم Batch Processing وتخزين مؤقت لتحسين الأداء
 */
class GradebookStatsService
{
    private const CACHE_TTL = 300; // 5 دقائق

    /**
     * جلب إحصائيات الفصل مع التخزين المؤقت
     */
    public function getClassStats(int $courseOfferingId): ClassStatsData
    {
        $cacheKey = "grading.stats.{$courseOfferingId}";

        return $this->rememberWithTags(
            ['grading', 'stats'],
            $cacheKey,
            self::CACHE_TTL,
            fn() => $this->calculateWithBatch($courseOfferingId)
        );
    }

    /**
     * حساب الإحصائيات باستخدام Batch Processing
     * 
     * - Query واحد ضخم بدلاً من Loop
     * - تحويل إلى Keyed Map للوصول O(1)
     * - الحسابات في الذاكرة
     */
    private function calculateWithBatch(int $courseOfferingId): ClassStatsData
    {
        // Query واحد لجلب كل الدرجات
        $allGrades = MonthlyGrade::where('course_offering_id', $courseOfferingId)
            ->select('student_id', 'category', 'score', 'max_score')
            ->get();

        if ($allGrades->isEmpty()) {
            return new ClassStatsData(
                average: 0,
                max: 0,
                min: 0,
                passingCount: 0,
                totalCount: 0,
                passingRate: 0,
            );
        }

        // تجميع حسب الطالب
        $studentTotals = $allGrades->groupBy('student_id')
            ->map(function (Collection $grades) {
                return $grades->sum('score');
            })
            ->values()
            ->toArray();

        return ClassStatsData::fromScores($studentTotals);
    }

    /**
     * حذف الكاش بعد تحديث الدرجات
     */
    public function invalidateCache(int $courseOfferingId): void
    {
        $this->flushTags(['grading', 'stats']);
        Cache::forget("grading.stats.{$courseOfferingId}");
    }

    /**
     * حساب مجموع درجات طالب معين
     */
    public function getStudentTotal(int $studentId, int $courseOfferingId): float
    {
        return MonthlyGrade::where('student_id', $studentId)
            ->where('course_offering_id', $courseOfferingId)
            ->sum('score');
    }

    /**
     * جلب إحصائيات شهر معين
     */
    public function getMonthStats(int $courseOfferingId, int $monthId): ClassStatsData
    {
        $grades = MonthlyGrade::where('course_offering_id', $courseOfferingId)
            ->where('gradebook_month_id', $monthId)
            ->get();

        $studentScores = $grades->groupBy('student_id')
            ->map(fn(Collection $g) => $g->sum('score'))
            ->values()
            ->toArray();

        return ClassStatsData::fromScores($studentScores);
    }

    private function rememberWithTags(array $tags, string $key, int $ttl, \Closure $callback): mixed
    {
        if ($this->cacheDriverSupportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    private function flushTags(array $tags): void
    {
        if ($this->cacheDriverSupportsTags()) {
            Cache::tags($tags)->flush();
        }
    }

    private function cacheDriverSupportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'dynamodb']);
    }
}
