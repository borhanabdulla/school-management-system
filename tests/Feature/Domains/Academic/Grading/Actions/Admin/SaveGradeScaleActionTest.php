<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Actions\Admin\SaveGradeScaleAction;
use App\Domains\Academic\Grading\Data\GradeScaleData;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\GradingPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaveGradeScaleActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => GradingPermissionsSeeder::class]);
    }

    #[Test]
    public function it_saves_valid_scale_successfully(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        $scale = [
            ['min' => 90, 'max' => 100, 'grade' => 'A', 'label' => 'ممتاز'],
            ['min' => 80, 'max' => 89, 'grade' => 'B', 'label' => 'جيد جداً'],
            ['min' => 70, 'max' => 79, 'grade' => 'C', 'label' => 'جيد'],
            ['min' => 60, 'max' => 69, 'grade' => 'D', 'label' => 'مقبول'],
            ['min' => 0, 'max' => 59, 'grade' => 'F', 'label' => 'ضعيف'],
        ];

        $data = new GradeScaleData(scale: $scale);
        $action = app(SaveGradeScaleAction::class);

        // Act
        $this->actingAs($admin);
        $action->execute($data);

        // Assert
        $savedScale = SystemSetting::get('grading.scale');
        $this->assertIsArray($savedScale);
        $this->assertCount(5, $savedScale);
    }

    #[Test]
    public function it_rejects_user_without_permission(): void
    {
        // Arrange
        $teacher = User::factory()->create();
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->assignRole($teacherRole);

        $scale = [
            ['min' => 90, 'max' => 100, 'grade' => 'A', 'label' => 'ممتاز'],
        ];

        $data = new GradeScaleData(scale: $scale);
        $action = app(SaveGradeScaleAction::class);

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
        $scale = [
            ['min' => 90, 'max' => 100, 'grade' => 'A', 'label' => 'ممتاز'],
        ];

        $data = new GradeScaleData(scale: $scale);
        $action = app(SaveGradeScaleAction::class);

        // Act & Assert
        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('يجب تسجيل الدخول');

        $action->execute($data);
    }
}
