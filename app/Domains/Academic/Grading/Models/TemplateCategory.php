<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// Assessment is in same namespace (Grading\Models)

class TemplateCategory extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'فئة التقييم';
    protected static string $modelPluralLabel = 'فئات التقييم';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'children' => 'فئات فرعية',
        'assessments' => 'تقييمات',
    ];

    protected $fillable = [
        'grading_template_id',
        'parent_id',
        'name',
        'weight',
        'max_raw_score',
        'calculation_type',
        'is_dynamic_weight',
        'is_locked',
        'pass_required',
        'pass_threshold',
        'order',
        'mapping_type',
        'is_readonly',
        'is_final_exam',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_raw_score' => 'decimal:2',
        'pass_threshold' => 'decimal:2',
        'is_dynamic_weight' => 'boolean',
        'is_locked' => 'boolean',
        'pass_required' => 'boolean',
        'is_readonly' => 'boolean',
        'is_final_exam' => 'boolean',
    ];

    /**
     * هل هذه الفئة قابلة للمزامنة التلقائية؟
     */
    public function isSyncable(): bool
    {
        return $this->mapping_type !== 'manual';
    }

    /**
     * هل يمكن التعديل اليدوي؟
     */
    public function isEditable(): bool
    {
        return !$this->is_readonly && $this->mapping_type === 'manual';
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GradingTemplate::class, 'grading_template_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(TemplateCategory::class, 'parent_id')->orderBy('order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
