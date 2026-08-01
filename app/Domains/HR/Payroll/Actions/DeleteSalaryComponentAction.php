<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\SalaryComponent;

class DeleteSalaryComponentAction
{
    public function execute(SalaryComponent $component): void
    {
        $component->delete();
    }
}
