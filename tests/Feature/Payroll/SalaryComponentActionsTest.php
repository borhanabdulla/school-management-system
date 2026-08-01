<?php

namespace Tests\Feature\Payroll;

use App\Domains\HR\Payroll\Actions\CreateSalaryComponentAction;
use App\Domains\HR\Payroll\Actions\UpdateSalaryComponentAction;
use App\Domains\HR\Payroll\Actions\DeleteSalaryComponentAction;
use App\Domains\HR\Payroll\Models\SalaryComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryComponentActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_salary_component(): void
    {
        $data = [
            'name' => 'Housing Allowance',
            'type' => 'allowance',
            'is_percentage' => false,
            'percentage_value' => null,
            'fixed_value' => 1000,
            'is_active' => true,
        ];

        $component = app(CreateSalaryComponentAction::class)->execute($data);

        $this->assertDatabaseHas('salary_components', [
            'id' => $component->id,
            'name' => 'Housing Allowance',
            'type' => 'allowance',
            'is_active' => 1,
        ]);
    }

    public function test_update_salary_component(): void
    {
        $component = SalaryComponent::create([
            'name' => 'Transport',
            'type' => 'allowance',
            'is_percentage' => false,
            'percentage_value' => null,
            'fixed_value' => 200,
            'is_active' => true,
        ]);

        $data = [
            'name' => 'Transport Updated',
            'type' => 'allowance',
            'is_percentage' => true,
            'percentage_value' => 5,
            'fixed_value' => null,
            'is_active' => false,
        ];

        $updated = app(UpdateSalaryComponentAction::class)->execute($component, $data);

        $this->assertDatabaseHas('salary_components', [
            'id' => $updated->id,
            'name' => 'Transport Updated',
            'type' => 'allowance',
            'is_active' => 0,
        ]);
    }

    public function test_delete_salary_component(): void
    {
        $component = SalaryComponent::create([
            'name' => 'Deduction',
            'type' => 'deduction',
            'is_percentage' => false,
            'percentage_value' => null,
            'fixed_value' => 50,
            'is_active' => true,
        ]);

        app(DeleteSalaryComponentAction::class)->execute($component);

        $this->assertDatabaseMissing('salary_components', ['id' => $component->id]);
    }
}
