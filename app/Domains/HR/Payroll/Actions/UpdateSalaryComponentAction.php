<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\SalaryComponent;

class UpdateSalaryComponentAction
{
    public function execute(SalaryComponent $component, array $data): SalaryComponent
    {
        $component->update($data);

        return $component->fresh();
    }
}
