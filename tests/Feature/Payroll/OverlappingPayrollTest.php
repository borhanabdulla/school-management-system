<?php

namespace Tests\Feature\Payroll;

use Tests\TestCase;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use App\Domains\HR\Payroll\Exceptions\DuplicatePayrollException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class OverlappingPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedActiveAcademicYearForDate(now());
    }

    public function test_cannot_create_overlapping_payroll_for_same_month()
    {
        // Arrange
        // نحتاج مستخدم لملء حقل generated_by
        $user = User::factory()->create();

        $data = new PayrollGenerationData(
            period_start: now()->startOfMonth(),
            period_end: now()->endOfMonth(),
            year: now()->year,
            month: now()->month,
            name: 'Original Batch'
        );

        // Act 1: إنشاء المسير الأول (نجاح)
        app(GeneratePayrollAction::class)->execute($data, $user->id);

        // Act 2: محاولة إنشاء مسير ثاني لنفس الشهر (فشل متوقع)
        $this->expectException(DuplicatePayrollException::class);

        app(GeneratePayrollAction::class)->execute($data, $user->id);
    }
}
