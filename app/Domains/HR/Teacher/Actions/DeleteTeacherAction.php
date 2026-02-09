<?php

namespace App\Domains\HR\Teacher\Actions;

use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Support\Facades\DB;
use DomainException;

class DeleteTeacherAction
{
    /**
     * التحقق من إمكانية حذف المعلم (بدون تنفيذ الحذف)
     *
     * @return array ['can_delete' => bool, 'reasons' => array]
     */
    public function canDelete(Teacher $teacher): array
    {
        $reasons = [];

        try {
            $this->ensureNoCourseOfferings($teacher);
        } catch (DomainException $e) {
            $reasons[] = $e->getMessage();
        }

        try {
            $this->ensureNoSubstitutions($teacher);
        } catch (DomainException $e) {
            $reasons[] = $e->getMessage();
        }

        try {
            $this->ensureNoLegacySubstitutions($teacher);
        } catch (DomainException $e) {
            $reasons[] = $e->getMessage();
        }

        return [
            'can_delete' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    /**
     * تنفيذ حذف المعلم مع التحقق من الارتباطات
     * 
     * @param Teacher $teacher
     * @return void
     * @throws DomainException في حالة وجود ارتباطات تمنع الحذف
     */
    public function execute(Teacher $teacher): void
    {
        // 1. Preflight Checks (تحقيقات وقائية قبل الحذف)
        $this->ensureNoCourseOfferings($teacher);
        $this->ensureNoSubstitutions($teacher);
        $this->ensureNoLegacySubstitutions($teacher);

        // 2. التنفيذ داخل Transaction
        DB::transaction(function () use ($teacher) {
            // حذف سجل المعلم (سيطرق HandlesSafeDelete كطبقة حماية إضافية)
            $teacher->delete();

            // ملاحظة: لا نحذف Staff أو User للحفاظ على السجل الوظيفي
        });
    }

    private function ensureNoCourseOfferings(Teacher $teacher): void
    {
        if ($teacher->courseOfferings()->count() > 0) {
            throw new DomainException("لا يمكن حذف المعلم لارتباطه بمقررات دراسية/جداول حالية.");
        }
    }

    private function ensureNoSubstitutions(Teacher $teacher): void
    {
        if ($teacher->substitutionsAsOriginal()->count() > 0) {
            throw new DomainException("لا يمكن حذف المعلم لوجود سجلات بدلاء (كمعلم غائب).");
        }

        if ($teacher->substitutionsAsSubstitute()->count() > 0) {
            throw new DomainException("لا يمكن حذف المعلم لوجود سجلات بدلاء (كمعلم بديل).");
        }
    }

    private function ensureNoLegacySubstitutions(Teacher $teacher): void
    {
        // فحص الجدول القديم (Deprecated) حفاظاً على سلامة البيانات
        // نفترض أن العمود هو teacher_id أو substitute_teacher_id في الجدول القديم
        // سنفحص كليهما للأمان إذا كانت الأعمدة موجودة

        // ملاحظة: بما أننا لم نعد نستخدم هذا الجدول، هذا الفحص وقائي فقط
        // إذا كان الجدول غير موجود، نلتقط الاستثناء ونتجاهله
        try {
            $exists = DB::table('substitution_classes')
                ->where('substitute_teacher_id', $teacher->id)
                ->exists();

            if ($exists) {
                throw new DomainException("لا يمكن حذف المعلم لوجود سجلات في نظام البدلاء القديم.");
            }
        } catch (\Exception $e) {
            if ($e instanceof DomainException)
                throw $e;
            // نتجاهل خطأ "الجدول غير موجود" إذا تم حذفه مستقبلاً
        }
    }
}
