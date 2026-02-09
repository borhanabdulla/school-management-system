<?php

namespace App\Domains\Academic\Control\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamCommittee extends Model
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'لجنة امتحانية';
    protected static string $modelPluralLabel = 'اللجان الامتحانية';
    protected static string $labelAttribute = 'name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'seatings' => 'تواجد طلاب',
    ];
    protected $fillable = [
        'exam_session_id',
        'name',
        'room',
        'capacity',
        'supervisor_id',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'supervisor_id');
    }

    public function seatings(): HasMany
    {
        return $this->hasMany(ExamSeating::class, 'committee_id');
    }

    public function getStudentsCountAttribute(): int
    {
        return $this->seatings()->count();
    }
}
