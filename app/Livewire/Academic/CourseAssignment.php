<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Infrastructure\Context\AcademicContextService;
use App\Domains\Academic\CourseOffering\Services\CourseOfferingService;
use App\Domains\Academic\CourseOffering\Actions\AssignTeacherAction;

#[Layout('layouts.app')]
class CourseAssignment extends Component
{
    // Public State (أقل ما يمكن لتقليل حجم الـ Payload)
    public int $classSectionId;
    public string $sectionName;
    public array $assignments = []; // مصفوفة بسيطة [subject_id => teacher_id]
    public array $subjects = [];    // قائمة لعرض المواد
    public int $currentYearId;      // نحتفظ بالـ ID فقط

    public function mount(
        $sectionId,
        CourseOfferingService $assignmentService,
        AcademicContextService $context
    ) {
        $this->classSectionId = $sectionId;

        // 1. استخدام AcademicContextService لجلب السنة النشطة
        $activeYear = $context->activeYear();

        if (!$activeYear) {
            abort(403, 'عذراً، لا توجد سنة دراسية نشطة حالياً. يرجى إعداد السنة الدراسية أولاً.');
        }

        $this->currentYearId = $activeYear->id;

        // 2. جلب بيانات الشعبة مع المواد في استعلام واحد (تحسين الأداء)
        $section = ClassSection::with([
            'grade.subjects' => function ($query) {
                $query->select('subjects.id', 'subjects.name', 'subjects.code')
                    ->orderBy('subjects.name');
            }
        ])->findOrFail($sectionId);

        $this->sectionName = $section->name . ' - ' . $section->grade->name;

        // 3. بناء المصفوفة الأولية (تمرير الشعبة المحملة مسبقاً)
        $matrix = $assignmentService->getAssignmentMatrixFromSection($section, $this->currentYearId);

        foreach ($matrix as $row) {
            $this->subjects[] = $row;
            // تعبئة القيم الحالية للـ Dropdowns
            $this->assignments[$row['subject_id']] = $row['assigned_teacher_id'];
        }
    }

    /**
     * قائمة المعلمين المحسوبة (Computed Property).
     * الميزة: لا يتم إرسال هذه القائمة الضخمة مع كل Request للواجهة.
     * يتم استدعاؤها فقط عند الحاجة ويتم تخزينها مؤقتاً.
     */
    #[Computed(persist: true, seconds: 3600)]
    public function teachers()
    {
        // استعلام خفيف جداً ومخصص للـ Dropdown
        return Teacher::query()
            ->active()
            ->join('staff', 'teachers.staff_id', '=', 'staff.id')
            ->whereNotNull('staff.user_id') // نتأكد أن لديهم حساب
            ->select('teachers.id', 'staff.first_name', 'staff.last_name')
            ->orderBy('staff.first_name')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->first_name . ' ' . $t->last_name
                ];
            });
    }

    /**
     * دالة التحديث الفوري عند تغيير القائمة المنسدلة
     * ✅ PR-2: المعلم مطلوب - لا يُسمح بإلغاء التعيين
     */
    public function updateAssignment($subjectId, AssignTeacherAction $action)
    {
        // التحقق من صحة البيانات
        if (!array_key_exists($subjectId, $this->assignments)) {
            return;
        }

        $teacherId = $this->assignments[$subjectId];

        // ✅ 1. رفض القيمة الفارغة (لا يُسمح بإلغاء التعيين)
        if (empty($teacherId)) {
            $this->dispatch('toast', message: 'يجب اختيار معلم - المعلم مطلوب لكل مادة', type: 'warning');
            return;
        }

        // ✅ 2. التحقق من أن القيمة رقمية صالحة
        if (!is_numeric($teacherId) || (int) $teacherId <= 0) {
            $this->dispatch('toast', message: 'قيمة غير صالحة للمعلم', type: 'error');
            return;
        }

        $teacherId = (int) $teacherId;

        try {
            // تنفيذ أكشن تعيين المعلم
            $action->execute($this->classSectionId, $subjectId, $teacherId, $this->currentYearId);

            // إشعار نجاح خفيف (Toast)
            $this->dispatch('toast', message: 'تم حفظ التغييرات', type: 'success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ 3. عرض رسائل التحقق بوضوح
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('toast', message: $firstError ?? 'خطأ في البيانات', type: 'error');

        } catch (\Exception $e) {
            // ✅ 4. التقاط أي أخطاء أخرى
            $this->dispatch('toast', message: 'حدث خطأ: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.academic.course-assignment');
    }
}
