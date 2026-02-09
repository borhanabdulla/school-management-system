<?php

namespace App\Domains\Academic\Results\Models;

use App\Domains\Academic\Grading\Models\TemplateCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermResultFailure extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_result_id',
        'template_category_id',
        'reason',
        'required_min',
        'actual_percentage',
    ];

    protected $casts = [
        'required_min' => 'decimal:2',
        'actual_percentage' => 'decimal:2',
    ];

    public function termResult(): BelongsTo
    {
        return $this->belongsTo(TermResult::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }
}
