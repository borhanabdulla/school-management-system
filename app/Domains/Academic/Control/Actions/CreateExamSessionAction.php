<?php

declare(strict_types=1);

namespace App\Domains\Academic\Control\Actions;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Models\ExamCommittee;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * CreateExamSessionAction - إنشاء جلسة اختبار
 * 
 * تُنشئ جلسة اختبار جديدة مع تعيين لجنة الاختبار
 */
class CreateExamSessionAction
{
    public function execute(array $data): ExamSession
    {
        return DB::transaction(function () use ($data) {
            $term = Term::query()
                ->lockForUpdate()
                ->findOrFail($data['term_id']);

            // 1. إنشاء جلسة الاختبار
            $session = ExamSession::create([
                'academic_year_id' => $term->academic_year_id,
                'term_id' => $data['term_id'],
                'subject_id' => $data['subject_id'],
                'grade_id' => $data['grade_id'],
                'exam_date' => $data['exam_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'] ?? null,
                'max_mark' => $data['max_mark'] ?? 100,
                'status' => 'scheduled',
            ]);

            // 2. تعيين اللجنة
            if (!empty($data['committee_members'])) {
                foreach ($data['committee_members'] as $member) {
                    ExamCommittee::create([
                        'exam_session_id' => $session->id,
                        'staff_id' => $member['staff_id'],
                        'role' => $member['role'] ?? 'member',
                    ]);
                }
            }

            return $session;
        });
    }
}
