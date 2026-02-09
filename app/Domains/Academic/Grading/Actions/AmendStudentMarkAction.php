<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions;

use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Data\GradeAmendmentData;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * AmendStudentMarkAction - إجراء تعديل درجة طالب
 *
 * يسمح للمدير بتعديل درجة طالب بعد إغلاق السنة الدراسية
 * مع تسجيل السبب وبيانات Audit
 *
 * ⚠️ يتطلب صلاحية: amend.grades
 * ⚠️ السبب إلزامي للتعديل
 */
class AmendStudentMarkAction
{
    /**
     * تعديل درجة طالب
     *
     * @param StudentMark $mark الدرجة المراد تعديلها
     * @param float $newScore الدرجة الجديدة
     * @param string $reason سبب التعديل (إلزامي)
     * @return StudentMark الدرجة المعدلة
     *
     * @throws GradingException إذا لم يكن لديه صلاحية أو لم يقدم سبب
     */
    public function execute(
        StudentMark $mark,
        float $newScore,
        string $reason
    ): StudentMark {
        // التحقق من صلاحية المستخدم
        $this->authorize();

        // التحقق من وجود سبب التعديل
        $this->validateReason($reason);

        return DB::transaction(function () use ($mark, $newScore, $reason) {
            $payload = GradeAmendmentData::forStudentMark($mark, $newScore, $reason, Auth::id());

            $mark->update([
                'raw_score' => $payload->newScore,
                'scaled_score' => $payload->newScore,
                'amended_by' => $payload->actorId,
                'amended_at' => now(),
                'amendment_reason' => $payload->reason,
            ]);

            $mark->refresh();

            $this->logAmendment($mark, $payload);

            return $mark;
        });
    }

    /**
     * تعديل درجة شهرية
     */
    public function amendMonthlyGrade(
        MonthlyGrade $grade,
        float $newScore,
        string $reason
    ): MonthlyGrade {
        // التحقق من الصلاحية
        $this->authorize();

        // التحقق من السبب
        $this->validateReason($reason);

        return DB::transaction(function () use ($grade, $newScore, $reason) {
            $payload = GradeAmendmentData::forMonthlyGrade($grade, $newScore, $reason, Auth::id());

            $grade->update([
                'score' => $payload->newScore,
                'amended_by' => $payload->actorId,
                'amended_at' => now(),
                'amendment_reason' => $payload->reason,
            ]);

            $this->logMonthlyAmendment($grade, $payload);

            return $grade->refresh();
        });
    }

    /**
     * التحقق من صلاحية المستخدم
     * 
     * يتطلب صلاحية 'grading.amend' لتعديل الدرجات بعد الإغلاق
     */
    protected function authorize(): void
    {
        if (!Auth::check()) {
            throw new GradingException('يجب تسجيل الدخول أولاً.');
        }

        // Spatie Permissions: التحقق من صلاحية تعديل الدرجات
        if (!Auth::user()->can('grading.amend')) {
            throw new GradingException('ليس لديك صلاحية تعديل الدرجات بعد الإغلاق. الصلاحية المطلوبة: grading.amend');
        }
    }

    /**
     * التحقق من سبب التعديل
     */
    protected function validateReason(string $reason): void
    {
        if (empty(trim($reason))) {
            throw new GradingException('سبب التعديل مطلوب ولا يمكن تركه فارغاً');
        }

        if (strlen($reason) < 10) {
            throw new GradingException('سبب التعديل يجب أن يكون 10 أحرف على الأقل');
        }
    }

    /**
     * تسجيل عملية التعديل
     */
    protected function logAmendment(
        StudentMark $mark,
        GradeAmendmentData $payload
    ): void {
        // TODO: تفعيل AuditLog Table لاحقاً
        // AuditLog::create([
        //     'action' => 'grade_amendment',
        //     'model_type' => StudentMark::class,
        //     'model_id' => $mark->id,
        //     'old_values' => ['score' => $oldScore],
        //     'new_values' => ['score' => $newScore],
        //     'user_id' => Auth::id(),
        //     'reason' => $reason,
        //     'ip_address' => request()->ip(),
        // ]);

        logger()->info('Grade amended', [
            'mark_id' => $mark->id,
            'student_id' => $mark->student_id,
            'old_score' => $payload->oldScore,
            'new_score' => $payload->newScore,
            'amended_by' => $payload->actorId,
            'reason' => $payload->reason,
            'term_id' => $payload->termId,
            'course_offering_id' => $payload->courseOfferingId,
        ]);
    }

    /**
     * تسجيل تعديل الدرجة الشهرية
     */
    protected function logMonthlyAmendment(
        MonthlyGrade $grade,
        GradeAmendmentData $payload
    ): void {
        logger()->info('Monthly grade amended', [
            'grade_id' => $grade->id,
            'student_id' => $grade->student_id,
            'old_score' => $payload->oldScore,
            'new_score' => $payload->newScore,
            'amended_by' => $payload->actorId,
            'reason' => $payload->reason,
            'term_id' => $payload->termId,
            'course_offering_id' => $payload->courseOfferingId,
        ]);
    }
}
