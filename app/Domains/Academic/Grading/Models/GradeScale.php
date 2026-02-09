<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScale extends Model
{
    use HandlesSafeDelete, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'سلم الدرجات';
    protected static string $modelPluralLabel = 'سلالم الدرجات';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'levels' => 'مستويات الدرجات',
    ];

    protected $fillable = [
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function levels(): HasMany
    {
        return $this->hasMany(GradeScaleLevel::class);
    }
}

