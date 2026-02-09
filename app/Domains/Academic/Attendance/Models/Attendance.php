<?php

namespace App\Domains\Academic\Attendance\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Database\Factories\Domains\Academic\Attendance\Models\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Shared\Models\User;

class Attendance extends Model
{
    use HasFactory, HasModelLabels;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasAcademicScope;

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AttendanceFactory
    {
        return AttendanceFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'الحضور';
    protected static string $modelPluralLabel = 'سجلات الحضور';

    protected $fillable = [
        'student_id',
        'class_section_id',
        'academic_year_id',
        'term_id', // ✅ PR1
        'date',
        'time_slot_id',
        'timetable_id', // ✅ PR1
        'status',
        'remarks',
        'delay_minutes',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * ✅ PR-1: الجدول الدراسي المرتبط بهذا الحضور
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }
}
