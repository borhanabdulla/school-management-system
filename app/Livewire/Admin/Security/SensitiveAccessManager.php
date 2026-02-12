<?php

namespace App\Livewire\Admin\Security;

use App\Domains\Academic\Grading\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('رمز الأمان الحساس')]
class SensitiveAccessManager extends Component
{
    public int $expiresInMinutes = 15;
    public ?string $generatedCode = null;
    public ?string $activeExpiresAt = null;
    public bool $hasActiveCode = false;
    public bool $activeExpired = false;

    public function mount(): void
    {
        $this->loadActiveCode();
    }

    public function generateCode(): void
    {
        $this->validate([
            'expiresInMinutes' => 'required|integer|min:1|max:1440',
        ]);

        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes($this->expiresInMinutes);

        SystemSetting::set('sensitive.access_code_hash', hash('sha256', $code), 'security');
        SystemSetting::set('sensitive.access_code_expires_at', $expiresAt->toIso8601String(), 'security');
        SystemSetting::set('sensitive.access_code_created_at', now()->toIso8601String(), 'security');
        SystemSetting::set('sensitive.access_code_created_by', (string) Auth::id(), 'security');

        $this->generatedCode = $code;
        $this->loadActiveCode();

        $this->dispatch('notify', message: 'تم توليد رمز جديد وتم إلغاء أي رمز سابق.');
    }

    public function clearGeneratedCode(): void
    {
        $this->generatedCode = null;
    }

    private function loadActiveCode(): void
    {
        $hash = SystemSetting::get('sensitive.access_code_hash');
        $expiresAt = SystemSetting::get('sensitive.access_code_expires_at');

        $this->hasActiveCode = false;
        $this->activeExpired = false;
        $this->activeExpiresAt = null;

        if (!$hash || !$expiresAt) {
            return;
        }

        try {
            $expiresAt = Carbon::parse($expiresAt);
        } catch (\Exception $e) {
            return;
        }

        $this->hasActiveCode = true;
        $this->activeExpired = $expiresAt->isPast();
        $this->activeExpiresAt = $expiresAt->format('Y-m-d H:i');
    }

    public function render()
    {
        return view('livewire.admin.security.sensitive-access-manager');
    }
}
