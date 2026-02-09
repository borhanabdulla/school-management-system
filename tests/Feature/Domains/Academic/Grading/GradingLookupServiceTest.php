<?php

namespace Tests\Feature\Domains\Academic\Grading;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test للتحقق من إصلاح Term Leakage في GradingLookupService
 */
class GradingLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    private GradingLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GradingLookupService::class);
        Cache::flush(); // تنظيف cache قبل كل test
    }

    #[Test]
    public function it_returns_term_specific_template_when_exists()
    {
        // Arrange
        $year = AcademicYear::factory()->create(['status' => 'active']);
        $term = Term::factory()->create(['academic_year_id' => $year->id, 'status' => 'active']);
        $grade = Grade::factory()->create();

        $termTemplate = GradingTemplate::factory()->create([
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'name' => 'Term-Specific Template',
        ]);

        // Act
        $result = $this->service->getTemplateForGrade($grade->id, $term->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($termTemplate->id, $result->id);
        $this->assertEquals('Term-Specific Template', $result->name);
    }

    #[Test]
    public function it_falls_back_to_year_level_template_when_term_specific_not_found()
    {
        // Arrange
        $year = AcademicYear::factory()->create(['status' => 'active']);
        $term = Term::factory()->create(['academic_year_id' => $year->id, 'status' => 'active']);
        $grade = Grade::factory()->create();

        // فقط year-level template (بدون term_id)
        $yearTemplate = GradingTemplate::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'term_id' => null, // year-level
            'name' => 'Year-Level Template',
        ]);

        // Act
        $result = $this->service->getTemplateForGrade($grade->id, $term->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($yearTemplate->id, $result->id);
        $this->assertEquals('Year-Level Template', $result->name);
    }

    #[Test]
    public function it_returns_null_when_no_template_exists()
    {
        // Arrange
        $year = AcademicYear::factory()->create(['status' => 'active']);
        $term = Term::factory()->create(['academic_year_id' => $year->id]);
        $grade = Grade::factory()->create();

        // Act
        $result = $this->service->getTemplateForGrade($grade->id, $term->id);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_caches_result_with_term_in_key()
    {
        // Arrange
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create(['academic_year_id' => $year->id]);
        $grade = Grade::factory()->create();

        $template = GradingTemplate::factory()->create([
            'grade_id' => $grade->id,
            'term_id' => $term->id,
        ]);

        $expectedCacheKey = "grading.template.grade.{$grade->id}.term.{$term->id}";

        // Act
        $this->service->getTemplateForGrade($grade->id, $term->id);

        // Assert - التحقق من وجود cache key يحتوي على termId
        $cached = Cache::get($expectedCacheKey);
        $this->assertNotNull($cached, "Cache key should contain term_id");
        $this->assertEquals($template->id, $cached->id);
    }

    #[Test]
    public function it_prefers_term_specific_over_year_level_template()
    {
        // Arrange
        $year = AcademicYear::factory()->create();
        $term = Term::factory()->create(['academic_year_id' => $year->id]);
        $grade = Grade::factory()->create();

        // إنشاء كلا النوعين
        $yearTemplate = GradingTemplate::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'term_id' => null,
            'name' => 'Year Template',
        ]);

        $termTemplate = GradingTemplate::factory()->create([
            'grade_id' => $grade->id,
            'term_id' => $term->id,
            'name' => 'Term Template',
        ]);

        // Act
        $result = $this->service->getTemplateForGrade($grade->id, $term->id);

        // Assert - يجب أن يختار term-specific
        $this->assertEquals($termTemplate->id, $result->id);
        $this->assertEquals('Term Template', $result->name);
    }
}
