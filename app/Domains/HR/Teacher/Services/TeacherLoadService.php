<?php

namespace App\Domains\HR\Teacher\Services;

use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

class TeacherLoadService
{
    /**
     * حساب عدد الحصص الأسبوعية للمعلم في ترم معين
     * 
     * @param int $teacherId
     * @param int $termId
     * @return int عدد الحصص المجدولة
     */
    public function calculateWeeklySessions(int $teacherId, int $termId): int
    {
        return Teacher::where('id', $teacherId)
            ->withCount([
                'timetableSessions' => function (Builder $query) use ($termId) {
                    // نفلتر الحصص المرتبطة بمقررات هذا الترم
                    $query->whereHas('courseOffering', function ($q) use ($termId) {
                        $q->where('term_id', $termId);
                    });
                }
            ])
            ->value('timetable_sessions_count') ?? 0;
    }

    /**
     * الحصول على تفاصيل الحمل للوحة التحكم أو العروض
     * 
     * @param Teacher|int $teacher
     * @param int $termId
     * @return array
     */
    public function getLoadDetails(Teacher|int $teacher, int $termId): array
    {
        if (is_numeric($teacher)) {
            $teacher = Teacher::find($teacher);
        }

        if (!$teacher) {
            return [
                'weekly_sessions' => 0,
                'max_weekly_classes' => 0,
                'percentage' => 0,
                'is_overloaded' => false
            ];
        }

        // إذا لم يتم تحميل الحصص مسبقاً، نحسبها
        $currentLoad = $teacher->timetable_sessions_count
            ?? $this->calculateWeeklySessions($teacher->id, $termId);

        $maxLoad = $teacher->max_weekly_classes;

        return [
            'weekly_sessions' => $currentLoad,
            'max_weekly_classes' => $maxLoad,
            'percentage' => $maxLoad > 0 ? round(($currentLoad / $maxLoad) * 100) : 0,
            'is_overloaded' => $currentLoad >= $maxLoad,
            'remaining_slots' => max(0, $maxLoad - $currentLoad)
        ];
    }
}
