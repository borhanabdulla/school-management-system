<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Actions\Admin\SaveGeneralGradingSettingsAction;
use App\Domains\Academic\Grading\Data\GeneralSettingsData;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\GradingPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaveGeneralGradingSettingsActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => GradingPermissionsSeeder::class]);
    }

    #[Test]
    public function it_saves_valid_settings_successfully(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        $data = new GeneralSettingsData(
            defaultPassScore: 50.0,
            graceMarksLimit: 3,
            termWeights: [1 => 40, 2 => 60]
        );

        $action = app(SaveGeneralGradingSettingsAction::class);

        // Act
        $this->actingAs($admin);
        $action->execute($data);

        // Assert - الإعدادات تم حفظها
        $this->assertEquals(50.0, SystemSetting::get('grading.default_pass_score'));
        $this->assertEquals(3, SystemSetting::get('grading.grace_marks_limit'));
    }

    #[Test]
    public function it_rejects_term_weights_not_equal_to_100(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        $data = new GeneralSettingsData(
            defaultPassScore: 50.0,
            graceMarksLimit: 3,
            termWeights: [1 => 40, 2 => 50] // المجموع = 90 (خطأ)
        );

        $action = app(SaveGeneralGradingSettingsAction::class);

        // Act & Assert
        $this->actingAs($admin);

        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('مجموع أوزان الفصول الدراسية يجب أن يساوي 100%');

        $action->execute($data);
    }

    #[Test]
    public function it_rejects_invalid_pass_score(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        $data = new GeneralSettingsData(
            defaultPassScore: 150.0, // أكثر من 100
            graceMarksLimit: 3,
            termWeights: [1 => 50, 2 => 50]
        );

        $action = app(SaveGeneralGradingSettingsAction::class);

        // Act & Assert
        $this->actingAs($admin);

        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('درجة النجاح الافتراضية يجب أن تكون بين 0 و 100');

        $action->execute($data);
    }

    #[Test]
    public function it_rejects_user_without_permission(): void
    {
        // Arrange
        $teacher = User::factory()->create();
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->assignRole($teacherRole);

        $data = new GeneralSettingsData(
            defaultPassScore: 50.0,
            graceMarksLimit: 3,
            termWeights: [1 => 50, 2 => 50]
        );

        $action = app(SaveGeneralGradingSettingsAction::class);

        // Act & Assert
        $this->actingAs($teacher);

        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('ليس لديك صلاحية');

        $action->execute($data);
    }

    #[Test]
    public function it_rejects_unauthenticated_user(): void
    {
        // Arrange
        $data = new GeneralSettingsData(
            defaultPassScore: 50.0,
            graceMarksLimit: 3,
            termWeights: [1 => 50, 2 => 50]
        );

        $action = app(SaveGeneralGradingSettingsAction::class);

        // Act & Assert
        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('يجب تسجيل الدخول');

        $action->execute($data);
    }
}
