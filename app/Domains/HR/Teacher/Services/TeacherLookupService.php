<?php

namespace App\Domains\HR\Teacher\Services;

use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * TeacherLookupService - خدمة البحث عن المعلمين
 * 
 * مسؤولة عن عمليات القراءة والبحث عن المعلمين
 * مع تفعيل التكييش (Caching)
 */
class TeacherLookupService
{
    public const CACHE_TTL = 3600; // 1 hour

    /**
     * جلب قائمة المعلمين مع الفلترة
     */
    public function getTeachersList(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        // ملاحظة: التكييش هنا قد يكون صعباً مع الفلاتر المتعددة،
        // لذا نستخدم الكاش فقط للقوائم الثابتة أو نعتمد على كاش الاستعلامات إذا لزم الأمر.
        // في هذه الحالة، سنقوم بتنفيذ الاستعلام مباشرة لأن الفلاتر ديناميكية جداً.

        return Teacher::query()
            ->with(['staff']) // Eager Load
            ->withCount('courseOfferings') // إحصائيات

            // 1. البحث العام (الاسم والبريد)
            // ✅ البريد من users عبر staff.user (لا يوجد email في staff)
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('staff', function ($staffQ) use ($search) {
                        $staffQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                        ->orWhereHas('staff.user', function ($userQ) use ($search) {
                            $userQ->where('email', 'like', "%{$search}%");
                        });
                });
            })

            // 2. البحث بالمادة
            ->when($filters['subject_name'] ?? null, function (Builder $query, $subject) {
                $query->whereHas('courseOfferings.subject', function ($q) use ($subject) {
                    $q->where('name', 'like', "%{$subject}%");
                });
            })

            // 3. فلتر الشعبة (الأكثر دقة)
            ->when($filters['class_section_id'] ?? null, function (Builder $query, $sectionId) {
                $query->whereHas('courseOfferings', function ($q) use ($sectionId) {
                    $q->where('class_section_id', $sectionId);
                });
            })

            // 4. فلتر الصف (يعمل فقط إذا لم يتم اختيار شعبة محددة)
            ->when(($filters['grade_id'] ?? null) && empty($filters['class_section_id']), function (Builder $query, $gradeId) {
                $query->whereHas('courseOfferings.classSection', function ($q) use ($gradeId) {
                    $q->where('grade_id', $gradeId);
                });
            })

            // 5. فلتر السنة الدراسية (بناءً على التواجد الوظيفي)
            // ✅ يستخدم status بدلاً من termination_date (غير موجود)
            ->when($filters['academic_year_id'] ?? null, function (Builder $query, $yearId) {
                $year = \App\Domains\Academic\AcademicYear\Models\AcademicYear::find($yearId);
                if ($year) {
                    $query->whereHas('staff', function ($q) use ($year) {
                        $q->where('joining_date', '<=', $year->end_date)
                            ->where(function ($sub) {
                                // الموظف نشط أو في إجازة (ليس منتهي الخدمة)
                                $sub->employed();
                            });
                    });
                }
            })

            // ✅ يستخدم created_at بدلاً من hire_date (غير موجود)
            ->latest('teachers.created_at')
            ->paginate($perPage);
    }

    /**
     * بحث سريع للقوائم المنسدلة (مع كاش)
     */
    public function searchTeachersForDropdown(string $query = ''): array
    {
        $cacheKey = 'teachers_search_' . md5($query);

        return Cache::remember($cacheKey, 300, function () use ($query) {
            return Teacher::query()
                ->whereHas('staff', function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get()
                ->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->staff->full_name ?? 'Unknown',
                    ];
                })
                ->toArray();
        });
    }
    public function getAvailableTeachersForSubject(int $relatedSubjectId, ?int $termId = null)
    {
        $termId = $termId ?? \App\Infrastructure\Context\AcademicContextService::getInstance()->activeTermId();

        // ✅ المفتاح يتضمن التخصص/المادة لكننا نستخدمه للكاش فقط
        $key = "lookup_teachers_subject_{$relatedSubjectId}_term_{$termId}";

        // ✅ PR-5: استخدام TTL (30 دقيقة) بدل الأبدي لتفادي البيانات القديمة
        return Cache::remember($key, now()->addMinutes(30), function () use ($termId) {
            // ✅ أزلنا email من select (لا يوجد في staff)
            return Teacher::with(['staff:id,first_name,last_name,user_id', 'staff.user:id,email'])
                ->whereHas('staff', fn($q) => $q->active())
                // ✅ PR-5: استخدام العلاقة الجديدة لحساب عدد الحصص
                ->withCount([
                    'timetableSessions' => function ($query) use ($termId) {
                        $query->whereHas('courseOffering', function ($q) use ($termId) {
                            $q->where('term_id', $termId);
                        });
                    }
                ])
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->staff->first_name . ' ' . $t->staff->last_name,
                    // ✅ البريد من user عبر staff
                    'email' => $t->staff->user?->email ?? '',
                    'specialization' => $t->specialization ?? 'غير محدد',
                    // ✅ PR-5: استخدام عدد الحصص كمقياس للحمل
                    'current_load' => $t->timetable_sessions_count ?? 0,
                    'max_load' => $t->max_weekly_classes,
                    'load_percentage' => $t->max_weekly_classes > 0
                        ? round((($t->timetable_sessions_count ?? 0) / $t->max_weekly_classes) * 100)
                        : 0,
                    'is_overloaded' => ($t->timetable_sessions_count ?? 0) >= $t->max_weekly_classes,
                ]);
        });
    }

    /**
     * Get teacher ID for user (Cached Forever)
     */
    public function getTeacherIdForUser(int $userId)
    {
        return Cache::rememberForever("teacher_id_user_{$userId}", function () use ($userId) {
            $user = User::find($userId);
            return $user?->staff?->teacher?->id;
        });
    }

    /**
     * Clear teacher cache.
     */
    public static function clearTeacherCache(int $userId): void
    {
        Cache::forget("teacher_id_user_{$userId}");
    }
}
