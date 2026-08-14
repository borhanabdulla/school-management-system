<?php

namespace App\Domains\HR\Teacher\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Infrastructure\Traits\InvalidatesCache;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;

class Teacher extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HandlesSafeDelete;
    use HasModelLabels;
    use InvalidatesCache;

    protected static function newFactory()
    {
        return \Database\Factories\TeacherFactory::new();
    }

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'معلم';
    protected static string $modelPluralLabel = 'معلمين';

    /**
     * العلاقات المحمية من الحذف
     */
    protected array $protectedRelations = [
        'courseOfferings' => 'مقررات دراسية',
        'substitutionsAsOriginal' => 'بدائل (كمعلم غائب)',
        'substitutionsAsSubstitute' => 'بدائل (كمعلم بديل)',
    ];

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['teachers', 'academic'];

    // ✅ يطابق السكيمة الفعلية: staff_id, specialization, max_weekly_classes, user_id
    // ❌ أُزيل bio (غير موجود في DB)
    protected $fillable = ['staff_id', 'specialization', 'max_weekly_classes', 'user_id'];

    // المعلم يتبع موظفاً
    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\HR\Staff\Models\Staff::class);
    }

    // اختصار للوصول للاسم مباشرة عبر المعلم
    // $teacher->full_name
    public function getFullNameAttribute(): string
    {
        return $this->staff->full_name ?? '';
    }

    // المواد التي يدرسها (الحصص الفعلية)
    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }

    // ✅ PR-5: علاقة مباشرة للحصص لحساب الحمل بدقة
    public function timetableSessions(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Domains\Academic\Timetable\Models\Timetable::class,
            CourseOffering::class,
            'teacher_id', // Foreign key on CourseOffering table...
            'course_offering_id', // Foreign key on Timetable table...
            'id', // Local key on Teacher table...
            'id' // Local key on CourseOffering table...
        );
    }


    // ✅ PR-6: علاقة البدائل حيث المعلم هو الغائب (original)
    public function substitutionsAsOriginal(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Substitution\Models\Substitution::class, 'original_teacher_id');
    }

    // ✅ PR-6: علاقة البدائل حيث المعلم هو البديل (substitute)
    public function substitutionsAsSubstitute(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Substitution\Models\Substitution::class, 'substitute_teacher_id');
    }

    // 1. الأداة الأولى: المدرسين النشطين فقط (للتدريس)
    // ✅ يستخدم status بدلاً من termination_date (غير موجود في DB)
    public function scopeActive(Builder $query): void
    {
        $query->whereHas('staff', fn($q) => $q->active());
    }

    // 1b. المدرسين ضمن النظام (نشط أو في إجازة، للإدارة)
    public function scopeEmployed(Builder $query): void
    {
        $query->whereHas('staff', fn($q) => $q->employed());
    }

    // 2. الأداة الثانية: المدرسين المتخصصين في مادة معينة
    public function scopeInSubject(Builder $query, $subjectId): void
    {
        $query->whereHas('courseOfferings', fn($q) => $q->where('subject_id', $subjectId));
    }

    // 3. الأداة الثالثة (الأكثر ذكاءً): المدرسين المتاحين في وقت وتاريخ معين
    // هنا دمجنا الـ Advanced SQL داخل الـ Scope
    public function scopeAvailableAt(Builder $query, $date, $timeSlotId): void
    {
        $query
            ->whereDoesntHave('substitutionsAsSubstitute', function ($q) use ($date, $timeSlotId) {
                $q->whereDate('date', $date)
                    ->whereHas('timetable', fn($inner) => $inner->where('time_slot_id', $timeSlotId));
            })
            ->whereDoesntHave('substitutionsAsOriginal', function ($q) use ($date, $timeSlotId) {
                $q->whereDate('date', $date)
                    ->whereHas('timetable', fn($inner) => $inner->where('time_slot_id', $timeSlotId));
            })
            ->whereDoesntHave('timetableSessions', function ($q) use ($timeSlotId) {
                $q->where('time_slot_id', $timeSlotId);
            });
    }
}
