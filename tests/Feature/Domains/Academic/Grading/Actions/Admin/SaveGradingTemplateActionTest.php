<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Actions\Admin\SaveGradingTemplateAction;
use App\Domains\Academic\Grading\Data\TemplateData;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grade\Models\Grade;
use App\Models\User;
use Database\Seeders\GradingPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaveGradingTemplateActionTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;
    private Grade $grade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => GradingPermissionsSeeder::class]);

        // إنشاء year و grade مشتركين لتجنب unique constraint
        $this->year = AcademicYear::factory()->create(['status' => 'pending']);
        $this->grade = Grade::factory()->create();
    }

    #[Test]
    public function it_creates_year_level_template_successfully(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        $data = new TemplateData(
            name: 'قالب السنة',
            gradeId: $this->grade->id,
            academicYearId: $this->year->id,
            termId: null // year-level template
        );

        $action = app(SaveGradingTemplateAction::class);

        // Act
        $this->actingAs($admin);
        $template = $action->execute(null, $data);

        // Assert
        $this->assertInstanceOf(GradingTemplate::class, $template);
        $this->assertEquals('قالب السنة', $template->name);
        $this->assertNull($template->term_id);
        $this->assertEquals($this->year->id, $template->academic_year_id);
    }

    #[Test]
    public function it_creates_term_specific_template_successfully(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        $term = Term::factory()->create(['academic_year_id' => $this->year->id]);

        $data = new TemplateData(
            name: 'قالب الترم الأول',
            gradeId: $this->grade->id,
            academicYearId: $this->year->id,
            termId: $term->id
        );

        $action = app(SaveGradingTemplateAction::class);

        // Act
        $this->actingAs($admin);
        $template = $action->execute(null, $data);

        // Assert
        $this->assertInstanceOf(GradingTemplate::class, $template);
        $this->assertEquals($term->id, $template->term_id);
        $this->assertEquals($this->year->id, $template->academic_year_id);
    }

    #[Test]
    public function it_rejects_template_with_term_from_different_year(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($adminRole);

        // إنشاء سنة ثانية مع ترم ينتمي لها
        $year2 = AcademicYear::factory()->create(['status' => 'active']);
        $term = Term::factory()->create(['academic_year_id' => $year2->id]);

        $data = new TemplateData(
            name: 'قالب خاطئ',
            gradeId: $this->grade->id,
            academicYearId: $this->year->id, // سنة مختلفة عن الترم
            termId: $term->id
        );

        $action = app(SaveGradingTemplateAction::class);

        // Act & Assert
        $this->actingAs($admin);

        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('لا ينتمي للسنة الدراسية المحددة');

        $action->execute(null, $data);
    }

    #[Test]
    public function it_rejects_user_without_permission(): void
    {
        // Arrange
        $teacher = User::factory()->create();
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->assignRole($teacherRole);

        $data = new TemplateData(
            name: 'قالب',
            gradeId: $this->grade->id,
            academicYearId: $this->year->id,
            termId: null
        );

        $action = app(SaveGradingTemplateAction::class);

        // Act & Assert
        $this->actingAs($teacher);

        $this->expectException(GradingException::class);
        $this->expectExceptionMessage('ليس لديك صلاحية');

        $action->execute(null, $data);
    }
}
