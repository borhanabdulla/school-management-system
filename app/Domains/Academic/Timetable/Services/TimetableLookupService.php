<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Services;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Data\SlotGeneratorConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * TimetableLookupService - خدمة قراءة بيانات الجدول الدراسي
 * 
 * هذه الخدمة للقراءة فقط. تستخدم AcademicContextService (school()) كمصدر وحيد للسنة النشطة.
 * تعتمد على الكاش لأن القوالب تُنشأ مرة واحدة في السنة.
 */
class TimetableLookupService
{
    private const CACHE_TTL = 60 * 60 * 24; // 24 ساعة

    /**
     * جلب قالب الجدول للصف من السنة النشطة
     */
    public function getTemplateForGrade(int $gradeId): ?TimetableTemplate
    {
        $yearId = school()->activeYearId();
        if (!$yearId) {
            return null;
        }

        return Cache::tags(['timetable', 'templates'])->remember(
            "timetable.template.grade.{$gradeId}.year.{$yearId}",
            self::CACHE_TTL,
            fn() => TimetableTemplate::query()
                ->whereHas('grades', fn($q) => $q->where('grade_id', $gradeId))
                ->where('academic_year_id', $yearId)
                ->active()
                ->with('timeSlots')
                ->first()
        );
    }

    /**
     * جلب القوالب النشطة للسنة الحالية
     */
    public function getActiveTemplates(): Collection
    {
        $yearId = school()->activeYearId();
        if (!$yearId) {
            return collect();
        }

        return Cache::tags(['timetable', 'templates'])->remember(
            "timetable.templates.active.year.{$yearId}",
            self::CACHE_TTL,
            fn() => TimetableTemplate::where('academic_year_id', $yearId)
                ->active()
                ->with(['timeSlots', 'grades'])
                ->get()
        );
    }

    /**
     * جلب جميع القوالب للسنة (بما فيها المسودات)
     */
    public function getAllTemplatesForYear(?int $yearId = null): Collection
    {
        $yearId = $yearId ?? school()->activeYearId();
        if (!$yearId) {
            return collect();
        }

        return TimetableTemplate::where('academic_year_id', $yearId)
            ->with(['timeSlots', 'grades', 'academicYear', 'educationalStage'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * جلب قالب بالـ ID
     */
    public function getTemplateById(int $id): ?TimetableTemplate
    {
        return TimetableTemplate::with(['timeSlots', 'grades'])->find($id);
    }

    /**
     * جلب الصفوف المعينة بالفعل لقوالب أخرى
     */
    public function getAssignedGradeIds(int $yearId, ?int $excludeTemplateId = null): array
    {
        return DB::table('grade_timetable_template')
            ->where('academic_year_id', $yearId)
            ->when($excludeTemplateId, fn($q) => $q->where('template_id', '!=', $excludeTemplateId))
            ->pluck('grade_id')
            ->toArray();
    }

    /**
     * توليد حصص باستخدام المولد الذكي
     */
    public function generateSlots(SlotGeneratorConfig $config, array $workingDays): array
    {
        return $config->generateSlotsForAllDays($workingDays);
    }

    /**
     * التحقق من صحة الحصص
     */
    public function validateSlots(array $slots): array
    {
        $errors = [];
        $slotsByDay = collect($slots)->groupBy('day_of_week');

        foreach ($slotsByDay as $day => $daySlots) {
            $sorted = $daySlots->sortBy('order_index')->values();

            for ($i = 0; $i < count($sorted) - 1; $i++) {
                $current = $sorted[$i];
                $next = $sorted[$i + 1];

                $currentEnd = strtotime($current['end_time'] ?? $current->endTime ?? '');
                $nextStart = strtotime($next['start_time'] ?? $next->startTime ?? '');

                if ($currentEnd > $nextStart) {
                    $errors[] = "تداخل في اليوم {$day}: الحصة #{$i} تنتهي بعد بدء الحصة #" . ($i + 1);
                }
            }
        }

        return $errors;
    }

    /**
     * إبطال كاش القوالب
     */
    public function invalidateCache(): void
    {
        Cache::tags(['timetable', 'templates'])->flush();
    }
}
