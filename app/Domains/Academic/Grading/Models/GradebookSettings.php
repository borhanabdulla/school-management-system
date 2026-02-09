<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HasAcademicScope;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Support\Str;

class GradebookSettings extends Model
{
    use HasAcademicScope, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'إعدادات دفتر العلامات';
    protected static string $modelPluralLabel = 'إعدادات دفاتر العلامات';

    protected $fillable = [
        'academic_year_id',
        'monthly_categories',
        'attendance_deduct_after',
        'attendance_deduct_per_absence',
        'attendance_max_score',
        'allow_custom_categories',
    ];

    protected $casts = [
        'monthly_categories' => 'array',
        'attendance_deduct_after' => 'integer',
        'attendance_deduct_per_absence' => 'decimal:2',
        'attendance_max_score' => 'decimal:2',
        'allow_custom_categories' => 'boolean',
    ];

    /**
     * الحصول على الإعدادات للسنة الحالية أو إنشاء افتراضية
     */
    public static function getForYear(int $academicYearId): self
    {
        return self::firstOrCreate(
            ['academic_year_id' => $academicYearId],
            [
                'monthly_categories' => self::getDefaultCategories(),
                'attendance_deduct_after' => 3,
                'attendance_deduct_per_absence' => 0.5,
                'attendance_max_score' => 5,
                'allow_custom_categories' => true,
            ]
        );
    }

    /**
     * الحصول على الإعدادات للسنة بدون إنشاء (قراءة فقط).
     */
    public static function findForYear(int $academicYearId): ?self
    {
        return self::where('academic_year_id', $academicYearId)->first();
    }

    /**
     * إنشاء إعدادات افتراضية غير محفوظة (للعرض فقط).
     */
    public static function makeDefault(int $academicYearId): self
    {
        return self::make([
            'academic_year_id' => $academicYearId,
            'monthly_categories' => self::getDefaultCategories(),
            'attendance_deduct_after' => 3,
            'attendance_deduct_per_absence' => 0.5,
            'attendance_max_score' => 5,
            'allow_custom_categories' => true,
        ]);
    }

    /**
     * التقسيمات الافتراضية
     */
    public static function getDefaultCategories(): array
    {
        return self::normalizeMonthlyCategories([
            ['label' => 'واجبات', 'max_score' => 5, 'is_default' => true],
            ['label' => 'شفهي', 'max_score' => 5, 'is_default' => true],
            ['label' => 'مواظبة', 'max_score' => 5, 'is_default' => true, 'is_attendance' => true],
            ['label' => 'تحريري', 'max_score' => 10, 'is_default' => true],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $categories
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeMonthlyCategories(array $categories): array
    {
        $normalized = [];
        $existingKeys = [];

        foreach ($categories as $category) {
            $label = (string) ($category['label'] ?? $category['name'] ?? '');
            $key = (string) ($category['key'] ?? '');

            if ($key === '') {
                $key = self::generateCategoryKey($label);
            }

            $key = self::ensureUniqueCategoryKey($key, $existingKeys);
            $existingKeys[] = $key;

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'max_score' => (float) ($category['max_score'] ?? 0),
                'is_default' => (bool) ($category['is_default'] ?? false),
                'is_attendance' => (bool) ($category['is_attendance'] ?? false),
            ];
        }

        return $normalized;
    }

    public static function generateCategoryKey(string $label): string
    {
        $slug = Str::slug($label, '_');
        if ($slug === '') {
            return 'cat_' . substr(md5($label), 0, 8);
        }

        return $slug;
    }

    /**
     * @param array<int, string> $existingKeys
     */
    public static function ensureUniqueCategoryKey(string $key, array $existingKeys): string
    {
        if (! in_array($key, $existingKeys, true)) {
            return $key;
        }

        $index = 2;
        $candidate = "{$key}_{$index}";
        while (in_array($candidate, $existingKeys, true)) {
            $index++;
            $candidate = "{$key}_{$index}";
        }

        return $candidate;
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
