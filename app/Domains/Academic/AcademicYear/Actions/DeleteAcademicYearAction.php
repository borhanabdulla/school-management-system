<?php

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;

class DeleteAcademicYearAction
{
    public function execute(int $id): void
    {
        DB::transaction(function () use ($id) {
            $year = AcademicYear::where('id', $id)->lockForUpdate()->firstOrFail();

            // التحقق من الحالة: الحذف مسموح فقط للسنة المسودة (Pending)
            if ($year->status !== AcademicYearStatus::Pending) {
                throw new YearNotDeletableException('لا يمكن حذف سنة دراسية تم تفعيلها سابقاً (نشطة، مغلقة، أو مؤرشفة).');
            }

            // التحقق من وجود تسجيلات تاريخية
            if ($year->enrollments()->exists()) {
                throw new YearNotDeletableException('لا يمكن حذف سنة تحتوي على تسجيلات طلاب');
            }

            if ($year->terms()->exists()) {
                throw new YearNotDeletableException('لا يمكن حذف سنة تحتوي على فصول دراسية');
            }

            if ($year->sections()->exists()) {
                throw new YearNotDeletableException('لا يمكن حذف سنة تحتوي على شعب دراسية');
            }

            if (method_exists($year, 'feeStructures') && $year->feeStructures()->exists()) {
                throw new YearNotDeletableException('لا يمكن حذف سنة تحتوي على هياكل رسوم');
            }

            if (method_exists($year, 'schoolEvents') && $year->schoolEvents()->exists()) {
                throw new YearNotDeletableException('لا يمكن حذف سنة تحتوي على فعاليات مدرسية');
            }

            // ملاحظة: تم إزالة الحذف القسري للبيانات المرتبطة (الرسوم، الجداول، إلخ).
            // يجب أن يتم التعامل مع ذلك عبر Events/Observers في النطاقات المعنية (Finance, Timetable).
            // عند استدعاء delete()، سيتم إطلاق حدث 'deleting'. إذا كان هناك بيانات مرتبطة تمنع الحذف،
            // يجب أن يقوم الـ Observer برمي استثناء أو إرجاع false (إذا كان مدعوماً).
            // أو الاعتماد على قيود المفتاح الأجنبي (Foreign Keys) لمنع الحذف.

            $year->delete();

            Log::info('Academic year deleted', ['year_id' => $year->id]);
        });
    }
}
