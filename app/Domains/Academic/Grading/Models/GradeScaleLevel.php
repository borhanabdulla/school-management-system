<?php

namespace App\Domains\Academic\Grading\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScaleLevel extends Model
{
    use HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'مستوى سلم الدرجات';
    protected static string $modelPluralLabel = 'مستويات سلم الدرجات';

    protected $fillable = [
        'grade_scale_id',
        'name',
        'letter',
        'min_percent',
        'max_percent',
    ];

    protected $casts = [
        'min_percent' => 'decimal:2',
        'max_percent' => 'decimal:2',
    ];

    public function scale(): BelongsTo
    {
        return $this->belongsTo(GradeScale::class, 'grade_scale_id');
    }
}
