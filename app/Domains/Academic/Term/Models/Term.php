<?php

namespace App\Domains\Academic\Term\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Timetable\Models\Timetable;

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

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'timetables' => 'جداول الحصص',
        'attendances' => 'سجلات الحضور',
        'gradebookMonths' => 'أشهر الدرجات',
        'termResults' => 'نتائج الترم',
        'courseOfferings' => 'عروض المواد',
        'examSessions' => 'جلسات الامتحان',
        'subjectGradingConfigs' => 'إعدادات الدرجات',
        'gradingTemplates' => 'قوالب الدرجات',
        'monthlyCategoryMappings' => 'تصنيفات شهرية',
        'studentMarks' => 'درجات الطلاب',
    ];

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

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function gradebookMonths(): HasMany
    {
        return $this->hasMany(GradebookMonth::class);
    }

    public function termResults(): HasMany
    {
        return $this->hasMany(TermResult::class);
    }

    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function subjectGradingConfigs(): HasMany
    {
        return $this->hasMany(SubjectGradingConfig::class);
    }

    public function gradingTemplates(): HasMany
    {
        return $this->hasMany(GradingTemplate::class);
    }

    public function monthlyCategoryMappings(): HasMany
    {
        return $this->hasMany(MonthlyCategoryMapping::class);
    }

    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }

    // Scopes
    public function scopeActive($query) // البحث عن الترم النشط
    {
        return $query->where('status', TermStatus::Active);
    }
}
