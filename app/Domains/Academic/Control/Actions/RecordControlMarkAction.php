<?php

declare(strict_types=1);

namespace App\Domains\Academic\Control\Actions;

use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Control\Models\ExamSession;
use Illuminate\Support\Facades\DB;

/**
 * RecordControlMarkAction - تسجيل درجات الضبط
 * 
 * تُسجل درجات الطلاب في جلسة اختبار معينة
 */
class RecordControlMarkAction
{
    /**
     * تسجيل درجات متعددة
     * 
     * @param ExamSession $session جلسة الاختبار
     * @param array $marks مصفوفة الدرجات [['student_id' => 1, 'raw_mark' => 85], ...]
     */
    public function execute(ExamSession $session, array $marks): int
    {
        return DB::transaction(function () use ($session, $marks) {
            $count = 0;

            foreach ($marks as $mark) {
                ControlMark::updateOrCreate(
                    [
                        'exam_session_id' => $session->id,
                        'student_id' => $mark['student_id'],
                    ],
                    [
                        'raw_mark' => $mark['raw_mark'],
                        'adjusted_mark' => $mark['adjusted_mark'] ?? $mark['raw_mark'],
                        'notes' => $mark['notes'] ?? null,
                        'recorded_by' => auth()->id(),
                        'recorded_at' => now(),
                    ]
                );
                $count++;
            }

            // تحديث حالة الجلسة
            if ($count > 0) {
                $session->update(['status' => 'graded']);
            }

            return $count;
        });
    }

    /**
     * تسجيل درجة طالب واحد
     */
    public function recordSingle(ExamSession $session, int $studentId, float $rawMark, ?string $notes = null): ControlMark
    {
        return ControlMark::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'student_id' => $studentId,
            ],
            [
                'raw_mark' => $rawMark,
                'adjusted_mark' => $rawMark,
                'notes' => $notes,
                'recorded_by' => auth()->id(),
                'recorded_at' => now(),
            ]
        );
    }
}
