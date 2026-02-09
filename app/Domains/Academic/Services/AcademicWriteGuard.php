<?php

declare(strict_types=1);

namespace App\Domains\Academic\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Infrastructure\Exceptions\ResourceNotFoundException;
use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * AcademicWriteGuard - حارس منع الكتابة بعد إغلاق السنة/الترم
 *
 * يُستخدم في جميع Actions التي تكتب بيانات أكاديمية للتأكد
 * من أن السنة أو الترم ليسا مغلقين.
 *
 * @example
 * // في أي Action
 * app(AcademicWriteGuard::class)->assertYearNotClosed($academicYearId);
 * app(AcademicWriteGuard::class)->assertTermNotCompleted($termId);
 */
class AcademicWriteGuard
{
    /**
     * منع الكتابة إذا السنة الدراسية مغلقة
     *
     * @throws ResourceNotFoundException إذا السنة غير موجودة
     * @throws InvalidOperationException إذا السنة مغلقة
     */
    public function assertYearNotClosed(int $academicYearId): void
    {
        $year = AcademicYear::where('id', $academicYearId)->first();

        if (!$year) {
            throw ResourceNotFoundException::forModel(AcademicYear::class, $academicYearId);
        }

        if ($year->status === AcademicYearStatus::Closed) {
            throw InvalidOperationException::cannotModify(
                'السنة الدراسية "' . $year->name . '"',
                'مغلقة ولا يمكن التعديل'
            );
        }
    }

    /**
     * منع الكتابة إذا الفصل الدراسي مكتمل (Completed)
     *
     * @throws ResourceNotFoundException إذا الترم غير موجود
     * @throws InvalidOperationException إذا الترم مكتمل أو السنة مغلقة
     */
    public function assertTermNotCompleted(int $termId): void
    {
        $term = Term::where('id', $termId)->first();

        if (!$term) {
            throw ResourceNotFoundException::forModel(Term::class, $termId);
        }

        if ($term->status === TermStatus::Completed) {
            throw InvalidOperationException::cannotModify(
                'الفصل الدراسي "' . $term->name . '"',
                'مكتمل ولا يمكن التعديل'
            );
        }

        // أيضًا تأكد أن السنة الأم ليست مغلقة
        $this->assertYearNotClosed($term->academic_year_id);
    }

    /**
     * دمج: تحقق من السنة والترم معًا
     *
     * @throws ResourceNotFoundException إذا السنة أو الترم غير موجود
     * @throws InvalidOperationException إذا السنة أو الترم مغلق
     */
    public function assertWritable(int $academicYearId, ?int $termId = null): void
    {
        $this->assertYearNotClosed($academicYearId);

        if ($termId) {
            $this->assertTermNotCompleted($termId);
        }
    }

    /**
     * التحقق مما إذا كانت السنة قابلة للكتابة (بدون رمي استثناء)
     */
    public function isWritable(int $academicYearId, ?int $termId = null): bool
    {
        try {
            $this->assertWritable($academicYearId, $termId);
            return true;
        } catch (InvalidOperationException $e) {
            return false;
        }
    }

    /**
     * الحصول على رسالة خطأ واضحة
     */
    public function getBlockedMessage(int $academicYearId, ?int $termId = null): string
    {
        $year = AcademicYear::find($academicYearId);
        $yearName = $year ? $year->name : 'غير معروفة';

        if ($termId) {
            $term = Term::find($termId);
            $termName = $term ? $term->name : 'غير معروف';

            if ($year && $year->status === AcademicYearStatus::Closed) {
                return "لا يمكن التعديل لأن السنة الدراسية '{$yearName}' مغلقة";
            }

            if ($term && $term->status === TermStatus::Completed) {
                return "لا يمكن التعديل لأن الفصل الدراسي '{$termName}' مكتمل";
            }
        }

        if ($year && $year->status === AcademicYearStatus::Closed) {
            return "لا يمكن التعديل لأن السنة الدراسية '{$yearName}' مغلقة";
        }

        return 'غير مسموح بالتعديل';
    }
}
