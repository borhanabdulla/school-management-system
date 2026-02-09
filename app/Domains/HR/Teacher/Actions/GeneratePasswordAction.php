<?php

namespace App\Domains\HR\Teacher\Actions;

use Illuminate\Support\Str;

class GeneratePasswordAction
{
    /**
     * توليد كلمة مرور قوية
     * 
     * @return string
     */
    public function execute(): string
    {
        // توليد كلمة مرور قوية: 12 حرف + أرقام + رموز
        return Str::password(12, letters: true, numbers: true, symbols: true);
    }
}
