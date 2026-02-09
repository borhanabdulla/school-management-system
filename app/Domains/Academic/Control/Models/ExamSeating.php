<?php

namespace App\Domains\Academic\Control\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Academic\Student\Models\Student;

class ExamSeating extends Model
{
    use HasFactory, HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'رقم الجلوس';
    protected static string $modelPluralLabel = 'أرقام الجلوس';
    protected static string $labelAttribute = 'seat_number';
    protected $fillable = [
        'exam_session_id',
        'student_id',
        'committee_id',
        'seat_number',
        'secret_number',
        'is_barred',
        'barred_reason',
        'is_withheld',
        'withhold_reason',
    ];

    protected $casts = [
        'is_barred' => 'boolean',
        'is_withheld' => 'boolean',
    ];

    // ==================== العلاقات ====================

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(ExamCommittee::class, 'committee_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ControlMark::class);
    }

    // ==================== البحث بالرقم السري ====================

    /**
     * البحث عن سجل جلوس بالرقم السري (للرصد الأعمى)
     */
    public static function findBySecretNumber(int $sessionId, string $secretNumber): ?self
    {
        return static::where('exam_session_id', $sessionId)
            ->where('secret_number', $secretNumber)
            ->first();
    }

    /**
     * البحث برقم الجلوس
     */
    public static function findBySeatNumber(int $sessionId, string $seatNumber): ?self
    {
        return static::where('exam_session_id', $sessionId)
            ->where('seat_number', $seatNumber)
            ->first();
    }
}
