<?php

namespace App\Livewire\Admin\Grading\Components;

use App\Domains\Academic\Grading\Adapters\GradingActionsAdapter;
use App\Domains\Academic\Grading\Data\GeneralSettingsData;
use Livewire\Component;

/**
 * GeneralGradingSettings - إدارة الإعدادات العامة للدرجات
 * 
 * @responsibility إعدادات النجاح وأوزان الفصول الدراسية
 */
class GeneralGradingSettings extends Component
{
    // Properties - نقل من GradingSettings.php
    public float $defaultPassScore = 50;
    public int $graceMarksLimit = 2;
    public array $termWeights = [];

    /**
     * Mount - تحميل الإعدادات عند التهيئة
     */
    public function mount(): void
    {
        $this->loadSettings();
    }

    /**
     * تحميل الإعدادات العامة من Service
     */
    public function loadSettings(): void
    {
        $data = app(GradingActionsAdapter::class)->loadGeneralSettings();

        $this->defaultPassScore = $data->defaultPassScore;
        $this->graceMarksLimit = $data->graceMarksLimit;
        $this->termWeights = $data->termWeights;
    }

    /**
     * حفظ الإعدادات العامة
     */
    public function save(): void
    {
        // Validation
        $this->validate([
            'defaultPassScore' => 'required|numeric|min:0|max:100',
            'graceMarksLimit' => 'required|integer|min:0|max:10',
            'termWeights.*' => 'required|numeric|min:0|max:100',
        ]);

        try {
            // استدعاء Adapter (الذي يستدعي Action)
            app(GradingActionsAdapter::class)->saveGeneralSettings(
                new GeneralSettingsData(
                    defaultPassScore: $this->defaultPassScore,
                    graceMarksLimit: $this->graceMarksLimit,
                    termWeights: $this->termWeights
                )
            );

            // إعادة تحميل البيانات
            $this->loadSettings();

            // Notification
            $this->dispatch('notify', message: 'تم حفظ الإعدادات العامة بنجاح', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.grading.components.general-grading-settings');
    }
}
