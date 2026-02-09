<?php

namespace App\Livewire\Admin\Promotion;

use App\Models\SystemSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PromotionSettings extends Component
{
    // قواعد الإكمال
    public int $maxFailedForConditional = 2;
    public string $conditionalDecision = 'promote'; // promote or repeat

    // الربط المالي
    public bool $requireFinancialClearanceForCertificate = true;

    // صلاحيات
    public bool $directorOnlyCanOverride = true;

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->maxFailedForConditional = SystemSetting::get('promotion.max_failed_for_conditional', 3);
        $this->conditionalDecision = SystemSetting::get('promotion.conditional_decision', 'promote');
        $this->requireFinancialClearanceForCertificate = SystemSetting::get('promotion.require_financial_clearance_for_certificate', true);
        $this->directorOnlyCanOverride = SystemSetting::get('promotion.director_only_can_override', true);
    }

    public function saveSettings()
    {
        $this->validate([
            'maxFailedForConditional' => 'required|integer|min:0|max:10',
            'conditionalDecision' => 'required|in:promote,repeat',
        ]);

        SystemSetting::set('promotion.max_failed_for_conditional', $this->maxFailedForConditional, 'promotion', 'integer');
        SystemSetting::set('promotion.conditional_decision', $this->conditionalDecision, 'promotion', 'string');
        SystemSetting::set('promotion.require_financial_clearance_for_certificate', $this->requireFinancialClearanceForCertificate, 'promotion', 'boolean');
        SystemSetting::set('promotion.director_only_can_override', $this->directorOnlyCanOverride, 'promotion', 'boolean');

        $this->dispatch('notify', message: 'تم حفظ إعدادات الترحيل بنجاح', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.promotion.promotion-settings');
    }
}
