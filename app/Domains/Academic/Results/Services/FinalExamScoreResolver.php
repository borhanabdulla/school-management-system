<?php

declare(strict_types=1);

namespace App\Domains\Academic\Results\Services;

use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Results\Models\FinalResult;

class FinalExamScoreResolver
{
    public function resolveForTerm(int $studentId, int $courseOfferingId, int $termId): float
    {
        $finalExamScore = FinalResult::query()
            ->join('exam_sessions', 'exam_sessions.id', '=', 'final_results.exam_session_id')
            ->where('final_results.student_id', $studentId)
            ->where('final_results.course_offering_id', $courseOfferingId)
            ->where('exam_sessions.term_id', $termId)
            ->whereNotNull('final_results.final_exam_score')
            ->orderByDesc('exam_sessions.created_at')
            ->orderByDesc('final_results.id')
            ->value('final_results.final_exam_score');

        if ($finalExamScore !== null) {
            return (float) $finalExamScore;
        }

        $controlScore = ControlMark::query()
            ->join('exam_seatings', 'exam_seatings.id', '=', 'control_marks.exam_seating_id')
            ->join('exam_sessions', 'exam_sessions.id', '=', 'exam_seatings.exam_session_id')
            ->where('exam_seatings.student_id', $studentId)
            ->where('control_marks.course_offering_id', $courseOfferingId)
            ->where('exam_sessions.term_id', $termId)
            ->whereNotNull('control_marks.score')
            ->orderByDesc('exam_sessions.created_at')
            ->orderByDesc('control_marks.id')
            ->value('control_marks.score');

        return (float) ($controlScore ?? 0);
    }
}
