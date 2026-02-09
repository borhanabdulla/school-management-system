<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * GradingLookupService - خدمة جلب بيانات الدرجات
 * 
 * تستخدم AcademicContextService (عبر school()) كمصدر وحيد للسنة النشطة.
 * تعتمد على الكاش لتقليل الاستعلامات (القوالب تُنشأ مرة واحدة في السنة/الفصل).
 */
class GradingLookupService
{
    private const CACHE_TTL = 60 * 60 * 24; // 24 ساعة

    /**
     * جلب قالب الدرجات للصف والترم المحدد.
     * 
     * يبحث أولاً عن قالب محدد للترم، ثم يعود للقالب على مستوى السنة إن لم يوجد.
     * 
     * @param int $gradeId معرف الصف
     * @param int $termId معرف الترم
     * @return GradingTemplate|null
     */
    public function getTemplateForGrade(int $gradeId, int $termId): ?GradingTemplate
    {
        return $this->rememberWithTags(
            ['grading', 'templates'],
            "grading.template.grade.{$gradeId}.term.{$termId}",
            self::CACHE_TTL,
            function () use ($gradeId, $termId) {
                // استعلام واحد مُحسّن: يجلب term مع academic_year_id
                $term = \App\Domains\Academic\Term\Models\Term::query()
                    ->select('id', 'academic_year_id')
                    ->where('id', $termId)
                    ->first();

                if (!$term) {
                    return null;
                }

                // Strategy 1: محاولة جلب القالب المحدد للترم (term-specific)
                $template = GradingTemplate::query()
                    ->where('grade_id', $gradeId)
                    ->where('term_id', $termId)
                    ->with(['categories' => fn($q) => $q->orderBy('order')])
                    ->first();

                // Strategy 2: Fallback إلى القالب على مستوى السنة (year-level)
                if (!$template) {
                    $template = GradingTemplate::query()
                        ->where('grade_id', $gradeId)
                        ->where('academic_year_id', $term->academic_year_id)
                        ->whereNull('term_id') // فقط القوالب على مستوى السنة
                        ->with(['categories' => fn($q) => $q->orderBy('order')])
                        ->first();
                }

                return $template;
            }
        );
    }

    /**
     * جلب جميع القوالب للسنة النشطة.
     */
    public function getActiveYearTemplates(): Collection
    {
        $yearId = school()->activeYearId();
        if (!$yearId) {
            return collect();
        }

        return $this->rememberWithTags(
            ['grading', 'templates'],
            "grading.templates.year.{$yearId}",
            self::CACHE_TTL,
            fn() => GradingTemplate::where('academic_year_id', $yearId)
                ->with('categories')
                ->get()
        );
    }

    /**
     * جلب سلم الدرجات الافتراضي (SystemSetting هو المصدر الوحيد).
     */
    public function getDefaultGradeScale(): ?array
    {
        return $this->rememberWithTags(
            ['grading', 'scale'],
            'grading.scale.default',
            self::CACHE_TTL,
            fn() => SystemSetting::get('grading.scale', null)
        );
    }

    /**
     * جلب جميع سلالم الدرجات (قائمة واحدة من SystemSetting).
     */
    public function getAllGradeScales(): Collection
    {
        return $this->rememberWithTags(
            ['grading', 'scale'],
            'grading.scales.all',
            self::CACHE_TTL,
            fn() => collect([SystemSetting::get('grading.scale', [])])
        );
    }

    /**
     * إبطال كاش القوالب (يُستدعى من Observer).
     */
    public function invalidateTemplatesCache(): void
    {
        $this->flushTags(['grading', 'templates']);
    }

    /**
     * إبطال كاش سلم الدرجات.
     */
    public function invalidateScaleCache(): void
    {
        $this->flushTags(['grading', 'scale']);
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
