<?php

namespace App\Domains\Academic\Grading\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Grading\Models\TemplateCategory;

class MonthlyCategoryMapping extends Model
{
    protected $fillable = [
        'academic_year_id',
        'term_id',
        'grade_id',
        'subject_id',
        'category_key',
        'template_category_id',
        'aggregation_rule',
        'missing_months_policy',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function templateCategory(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class);
    }
}
