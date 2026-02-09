<?php

namespace App\Domains\Academic\Control\Services;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Results\Models\FinalResult;
use Illuminate\Support\Collection;

final class FinalResultsViewerService
{
    public function subjects(ExamSession $session): Collection
    {
        return CourseOffering::where('term_id', $session->term_id)
            ->with(['subject', 'classSection.grade'])
            ->get()
            ->sortBy('subject.name');
    }

    public function results(ExamSession $session, ?int $courseOfferingId, string $statusFilter): Collection
    {
        $query = FinalResult::where('exam_session_id', $session->id)
            ->with([
                'student.seatings' => function ($q) use ($session) {
                    $q->where('exam_session_id', $session->id);
                },
                'courseOffering.subject',
                'courseOffering.classSection.grade',
            ]);

        if ($courseOfferingId) {
            $query->where('course_offering_id', $courseOfferingId);
        }

        if ($statusFilter === \App\Domains\Academic\Results\Enums\FinalResultStatus::Pass->value) {
            $query->where('status', \App\Domains\Academic\Results\Enums\FinalResultStatus::Pass);
        } elseif ($statusFilter === \App\Domains\Academic\Results\Enums\FinalResultStatus::Fail->value) {
            $query->where('status', \App\Domains\Academic\Results\Enums\FinalResultStatus::Fail);
        }

        return $query->orderBy('total_score', 'desc')->get();
    }

    public function stats(ExamSession $session, ?int $courseOfferingId): array
    {
        $base = FinalResult::where('exam_session_id', $session->id);

        if ($courseOfferingId) {
            $base->where('course_offering_id', $courseOfferingId);
        }

        $total = (clone $base)->count();
        $passed = (clone $base)->where('status', \App\Domains\Academic\Results\Enums\FinalResultStatus::Pass)->count();
        $failed = (clone $base)->where('status', \App\Domains\Academic\Results\Enums\FinalResultStatus::Fail)->count();
        $absent = (clone $base)->where('status', \App\Domains\Academic\Results\Enums\FinalResultStatus::Absent)->count();
        $avg = (clone $base)->avg('total_score');

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'absent' => $absent,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
            'average' => round($avg ?? 0, 1),
        ];
    }
}
