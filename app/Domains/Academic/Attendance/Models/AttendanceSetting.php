<?php

namespace App\Domains\Academic\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use App\Infrastructure\Traits\HasModelLabels;

class AttendanceSetting extends Model
{
    use HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'إعداد الحضور';
    protected static string $modelPluralLabel = 'إعدادات الحضور';

    protected $fillable = [
        'academic_year_id',
        'mode',
        'responsible_role',
        'late_tolerance',
    ];

    protected $casts = [
        'mode' => AttendanceMode::class,
        'responsible_role' => AttendanceResponsibility::class,
        'late_tolerance' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}

