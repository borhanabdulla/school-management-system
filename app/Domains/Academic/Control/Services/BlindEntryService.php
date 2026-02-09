<?php

namespace App\Domains\Academic\Control\Services;

use App\Domains\Academic\Control\Models\ControlMark;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Control\Exceptions\ExamSessionNotActiveException;
use App\Domains\Academic\Control\Exceptions\InvalidSecretNumberException;
use App\Domains\Academic\Control\Exceptions\StudentBarredException;
use App\Domains\Academic\Control\Exceptions\InvalidScoreException;
use App\Domains\Academic\Control\Exceptions\SubjectNotFoundException;
use App\Domains\Academic\Control\Exceptions\TermMismatchException;
use App\Domains\Academic\Grading\Services\SubjectScorePolicyResolver;
use Illuminate\Support\Facades\Auth;

class BlindEntryService
{
    /**
     * رصد درجة بالرقم السري (الرصد الأعمى)
     *
     * @param ExamSession $session الدورة الامتحانية
     * @param string $secretNumber الرقم السري
     * @param int $courseOfferingId معرف المادة
     * @param float|null $score الدرجة
     * @param bool $isAbsent هل غائب؟
     * @return ControlMark
     * @throws \Exception إذا كان الرقم السري غير صحيح
     */
    public function submitGrade(ExamSession $session,string $secretNumber,
        int $courseOfferingId,?float $score,bool $isAbsent = false): ControlMark {
        // 1. التحقق من حالة الدورة
        if (!$session->isActive()) {
            throw new ExamSessionNotActiveException();
        }

        // 2. البحث عن سجل الجلوس بالرقم السري
        $seating = ExamSeating::findBySecretNumber($session->id, $secretNumber);

        if (!$seating) {
            throw new InvalidSecretNumberException($secretNumber, $session->id);
        }

        // 3. التحقق من الحرمان
        if ($seating->is_barred) {
            throw new StudentBarredException($seating->barred_reason);
        }

        // 4. التحقق من الدرجة العظمى للمادة
        $courseOffering = CourseOffering::find($courseOfferingId);
        if (!$courseOffering) {
            throw new SubjectNotFoundException($courseOfferingId);
        }
        if ((int) $courseOffering->term_id !== (int) $session->term_id) {
            throw new TermMismatchException($courseOfferingId, $session->term_id, $courseOffering->term_id);
        }

        // الحصول على الدرجة العظمى من قالب الدرجات أو قيمة افتراضية
        $maxScore = $this->getMaxScoreForSubject($courseOffering);

        if ($score !== null && $score > $maxScore) {
            throw InvalidScoreException::exceedsMax($score, $maxScore);
        }

        if ($score !== null && $score < 0) {
            throw InvalidScoreException::negative();
        }

        // 5. حفظ أو تحديث الدرجة
        $mark = ControlMark::updateOrCreate(
            [
                'exam_seating_id' => $seating->id,
                'course_offering_id' => $courseOfferingId,
            ],
            [
                'score' => $isAbsent ? null : $score,
                'is_absent' => $isAbsent,
                'entered_by' => Auth::id(),
            ]
        );

        return $mark;
    }

    /**
     * جلب قائمة المواد المتاحة للرصد في دورة معينة
     */
    public function getAvailableSubjects(ExamSession $session): \Illuminate\Support\Collection
    {
        return CourseOffering::where('term_id', $session->term_id)
            ->with(['subject', 'classSection.grade'])
            ->get()
            ->groupBy('subject.name');
    }

    /**
     * جلب الدرجة العظمى للمادة (من قالب الدرجات)
     * ⚠️ هذا تبسيط - يجب أن يُجلب من TemplateCategory للاختبار النهائي
     */
    protected function getMaxScoreForSubject(CourseOffering $courseOffering): float
    {
        $termId = $courseOffering->term_id;
        if (! $termId) {
            return 100.0;
        }

        return app(SubjectScorePolicyResolver::class)
            ->resolveMaxScore($courseOffering, $termId);
    }

    /**
     * التحقق من صحة الرقم السري فقط (بدون رصد)
     */
    public function validateSecretNumber(ExamSession $session, string $secretNumber): ?ExamSeating
    {
        return ExamSeating::findBySecretNumber($session->id, $secretNumber);
    }

    /**
     * إحصائيات الرصد لمادة معينة
     */
    public function getSubjectStats(ExamSession $session, int $courseOfferingId): array
    {
        $total = ExamSeating::where('exam_session_id', $session->id)->count();

        $entered = ControlMark::whereHas('seating', fn($q) => $q->where('exam_session_id', $session->id))
            ->where('course_offering_id', $courseOfferingId)
            ->whereNotNull('score')
            ->count();

        $absent = ControlMark::whereHas('seating', fn($q) => $q->where('exam_session_id', $session->id))
            ->where('course_offering_id', $courseOfferingId)
            ->where('is_absent', true)
            ->count();

        return [
            'total' => $total,
            'entered' => $entered,
            'absent' => $absent,
            'remaining' => $total - $entered - $absent,
            'percentage' => $total > 0 ? round(($entered + $absent) / $total * 100, 1) : 0,
        ];
    }
}
