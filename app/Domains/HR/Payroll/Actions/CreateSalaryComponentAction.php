<?php

namespace App\Domains\HR\Payroll\Actions;

use App\Domains\HR\Payroll\Models\SalaryComponent;

class CreateSalaryComponentAction
{
    public function execute(array $data): SalaryComponent
    {
        return SalaryComponent::create($data);
    }
}
