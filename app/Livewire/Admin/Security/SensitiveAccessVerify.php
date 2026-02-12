<?php

namespace App\Livewire\Admin\Security;

use App\Infrastructure\Security\SensitiveAccess;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('تأكيد دخول الصفحات الحساسة')]
class SensitiveAccessVerify extends Component
{
    public string $code = '';
    public bool $hasActiveCode = false;
    public bool $activeExpired = false;
    public ?string $activeExpiresAt = null;

    public function mount(): void
    {
        $this->loadActiveCode();
    }

    public function verify(): void
    {
        $this->validate([
            'code' => 'required|digits:6',
        ]);

        $active = SensitiveAccess::getActiveCode();

        if (!$active) {
            $this->addError('code', 'لا يوجد رمز فعال حالياً. يرجى توليد رمز جديد.');
            return;
        }

        if (now()->greaterThan($active['expires_at'])) {
            $this->addError('code', 'الرمز منتهي الصلاحية. يرجى توليد رمز جديد.');
            return;
        }

        if (!SensitiveAccess::verifyCode($this->code)) {
            $this->addError('code', 'الرمز غير صحيح.');
            return;
        }

        SensitiveAccess::markVerified(request());

        $this->code = '';

        $target = session()->pull('sensitive_access_intended', route('dashboard'));
        $this->redirect($target, navigate: true);
    }

    private function loadActiveCode(): void
    {
        $active = SensitiveAccess::getActiveCode();

        $this->hasActiveCode = false;
        $this->activeExpired = false;
        $this->activeExpiresAt = null;

        if (!$active) {
            return;
        }

        $expiresAt = $active['expires_at'];

        if (!$expiresAt instanceof Carbon) {
            return;
        }

        $this->hasActiveCode = true;
        $this->activeExpired = $expiresAt->isPast();
        $this->activeExpiresAt = $expiresAt->format('Y-m-d H:i');
    }

    public function render()
    {
        return view('livewire.admin.security.sensitive-access-verify');
    }
}
