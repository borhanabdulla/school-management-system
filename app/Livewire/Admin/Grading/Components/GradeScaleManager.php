<?php

namespace App\Livewire\Admin\Grading\Components;

use App\Domains\Academic\Grading\Adapters\GradingActionsAdapter;
use App\Domains\Academic\Grading\Data\GradeScaleData;
use Livewire\Component;

/**
 * GradeScaleManager - إدارة سلم التقديرات
 * 
 * @responsibility إدارة Grade Scale (A, B, C+, etc.)
 */
class GradeScaleManager extends Component
{
    // Properties - نقل من GradingSettings.php
    public array $gradeScale = [];
    public ?string $externalError = null;

    /**
     * Mount - تحميل السلم عند التهيئة
     */
    public function mount(): void
    {
        $this->loadScale();
    }

    /**
     * تحميل سلم الدرجات من Service
     */
    public function loadScale(): void
    {
        $data = app(GradingActionsAdapter::class)->loadGradeScale();
        $this->gradeScale = $data->scale;
    }

    /**
     * حفظ سلم الدرجات
     */
    public function save(): void
    {
        // Clear previous errors
        $this->resetErrorBag('gradeScale');

        try {
            // استدعاء Adapter (الذي يستدعي Action)
            app(GradingActionsAdapter::class)->saveGradeScale(
                new GradeScaleData(scale: $this->gradeScale)
            );

            // إعادة تحميل البيانات
            $this->loadScale();

            // Notification
            $this->dispatch('notify', message: 'تم حفظ سلم التقديرات بنجاح', type: 'success');
        } catch (\Exception $e) {
            $this->addError('gradeScale', $e->getMessage());
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.grading.components.grade-scale-manager');
    }
}
