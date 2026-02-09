<?php

namespace App\Domains\Academic\Term\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Enums\TermStatus;

class Term extends Model
{
    use HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;

    protected static function newFactory()
    {
        return \Database\Factories\TermFactory::new();
    }

    protected static string $modelLabel = 'فصل دراسي';
    protected static string $modelPluralLabel = 'فصول دراسية';

    protected $fillable = ['academic_year_id', 'name', 'start_date', 'end_date', 'order_index', 'status',];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'order_index' => 'integer',
        'status' => TermStatus::class,
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scopes
    public function scopeActive($query) // البحث عن الترم النشط
    {
        return $query->where('status', TermStatus::Active);
    }
}
