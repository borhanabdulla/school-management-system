<?php

namespace App\Domains\HR\Shared\Services;

use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\HR\Substitution\Services\SubstitutionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HRDashboardService
{
    /**
     * Cache keys for event-based invalidation
     */
    private const CACHE_KEY_STATS = 'hr_dashboard_stats';
    private const CACHE_KEY_WEEK_LEAVES = 'hr_dashboard_week_leaves';
    private const CACHE_KEY_AFFECTED_CLASSES = 'hr_dashboard_affected_classes_';
    private const CACHE_KEY_HEATMAP = 'hr_dashboard_heatmap_';
    private const CACHE_TTL_DAY = 86400; // 24 hours
    private const CACHE_TTL_HOUR = 3600; // 1 hour
    private const CACHE_TTL_SHORT = 600; // 10 minutes

    /**
     * Request-level cache for shared data within single request
     */
    private ?Collection $weekLeavesCache = null;
    private ?array $pendingStatsCache = null;

    /**
     * مسح جميع الكاش المتعلق بلوحة الموارد البشرية
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_STATS);
        Cache::forget(self::CACHE_KEY_WEEK_LEAVES);
        Cache::forget('hr_stats_summary');

        // مسح كاش الحصص المتأثرة للأسبوع الحالي والقادم
        $today = now();
        for ($i = 0; $i < 14; $i++) {
            Cache::forget(self::CACHE_KEY_AFFECTED_CLASSES . $today->copy()->addDays($i)->format('Y-m-d'));
        }

        // مسح كاش التقويم (للشهر الحالي والقادم والسابق)
        Cache::forget('hr_dashboard_calendar_' . $today->format('Y-m'));
        Cache::forget('hr_dashboard_calendar_' . $today->copy()->addMonth()->format('Y-m'));
        Cache::forget('hr_dashboard_calendar_' . $today->copy()->subMonth()->format('Y-m'));
    }

    /**
     * جلب الإجازات المعتمدة للأسبوع (مركزي - يُستخدم من عدة methods)
     * مع Eager Loading لحل N+1
     */
    protected function getWeekApprovedLeaves(): Collection
    {
        // Request-level cache
        if ($this->weekLeavesCache !== null) {
            return $this->weekLeavesCache;
        }

        // Application-level cache
        return $this->weekLeavesCache = Cache::remember(self::CACHE_KEY_WEEK_LEAVES, self::CACHE_TTL_SHORT, function () {
            $startDate = now()->startOfDay();
            $endDate = now()->addDays(7)->endOfDay();

            return LeaveRequest::where('status', 'approved')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                // Eager load teacher relationship to prevent N+1
                ->with(['staff.teacher'])
                ->get();
        });
    }

    /**
     * جلب إحصائيات الطلبات المعلقة (مركزي) - استعلام واحد بدلاً من 3
     */
    protected function getPendingStats(): array
    {
        if ($this->pendingStatsCache !== null) {
            return $this->pendingStatsCache;
        }

        // تخزين الإحصائيات لمدة 10 دقائق
        return $this->pendingStatsCache = Cache::remember('hr_stats_summary', self::CACHE_TTL_SHORT, function () {
            $urgentDate = now()->addDay();
            $oldDate = now()->subHours(48);

            $stats = LeaveRequest::where('status', 'pending')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN start_date <= ? THEN 1 ELSE 0 END) as urgent,
                    SUM(CASE WHEN created_at < ? THEN 1 ELSE 0 END) as old
                ", [$urgentDate, $oldDate])
                ->first();

            return [
                'total' => (int) ($stats->total ?? 0),
                'urgent' => (int) ($stats->urgent ?? 0),
                'old' => (int) ($stats->old ?? 0),
            ];
        });
    }

    // ... (getActiveAlerts remains same)

    /**
     * جلب الحصص المتأثرة بالغياب ليوم محدد
     */
    public function getAffectedClasses(?Carbon $date = null): Collection
    {
        $targetDate = $date ?? now();
        $cacheKey = self::CACHE_KEY_AFFECTED_CLASSES . $targetDate->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_TTL_DAY, function () use ($targetDate) {
            $dayOfWeek = $targetDate->dayOfWeek;

            // 1. جلب الإجازات المعتمدة لهذا اليوم
            $approvedLeaves = LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', $targetDate)
                ->whereDate('end_date', '>=', $targetDate)
                ->with([
                    'staff.teacher.courseOfferings' => function ($query) use ($dayOfWeek) {
                        $query->whereHas('timetables.timeSlot', function ($q) use ($dayOfWeek) {
                            $q->where('day_of_week', $dayOfWeek);
                        });
                    },
                    'staff.teacher.courseOfferings.timetables' => function ($query) use ($dayOfWeek) {
                        $query->whereHas('timeSlot', function ($q) use ($dayOfWeek) {
                            $q->where('day_of_week', $dayOfWeek);
                        });
                    },
                    'staff.teacher.courseOfferings.timetables.timeSlot',
                    'staff.teacher.courseOfferings.subject',
                    'staff.teacher.courseOfferings.classSection.grade',
                ])
                ->get();

            $affectedClasses = collect();

            // إنشاء instance من SubstitutionService للتحقق من البدلاء
            $substitutionService = app(SubstitutionService::class);

            // تجميع كل معرفات الحصص المتأثرة أولاً
            $timetablesToCheck = [];
            $affectedItems = [];

            foreach ($approvedLeaves as $leave) {
                $teacher = $leave->staff?->teacher;
                if (!$teacher)
                    continue;

                foreach ($teacher->courseOfferings as $offering) {
                    foreach ($offering->timetables as $timetable) {
                        if ($timetable->timeSlot && $timetable->timeSlot->day_of_week === $dayOfWeek) {
                            $timetablesToCheck[] = $timetable->id;
                            $affectedItems[] = [
                                'leave' => $leave,
                                'teacher' => $teacher,
                                'offering' => $offering,
                                'timetable' => $timetable,
                            ];
                        }
                    }
                }
            }

            // جلب جميع البدائل دفعة واحدة
            $substitutions = $substitutionService->getSubstitutionsForTimetables($timetablesToCheck, $targetDate);

            foreach ($affectedItems as $item) {
                $timetable = $item['timetable'];
                $substitute = $substitutions->get($timetable->id);

                $affectedClasses->push([
                    'leave_id' => $item['leave']->id,
                    'teacher_id' => $item['teacher']->id,
                    'teacher_name' => $item['leave']->staff->full_name,
                    'subject_name' => $item['offering']->subject?->name ?? 'غير محدد',
                    'class_section_name' => $item['offering']->classSection?->full_name ?? 'غير محدد',
                    'time_slot' => $timetable->timeSlot->time_range,
                    'time_slot_label' => $timetable->timeSlot->label,
                    'order_index' => $timetable->timeSlot->order_index,
                    'timetable_id' => $timetable->id,
                    'has_substitute' => $substitute !== null,
                    'substitute_name' => $substitute?->substituteTeacher?->full_name,
                    'substitute_status' => $substitute?->status,
                ]);
            }

            return $affectedClasses->sortBy('order_index')->values();
        });
    }

    /**
     * جلب بيانات التقويم (Heatmap)
     */
    /**
     * بيانات التقويم الشهري (مع التخزين المؤقت)
     */
    public function getCalendarData(Carbon $calendarDate): array
    {
        $monthKey = $calendarDate->format('Y-m');
        $cacheKey = 'hr_dashboard_calendar_' . $monthKey;

        return Cache::remember($cacheKey, self::CACHE_TTL_HOUR, function () use ($calendarDate) {
            $startOfMonth = $calendarDate->copy()->startOfMonth();
            $endOfMonth = $calendarDate->copy()->endOfMonth();

            // جلب الإجازات المعتمدة للشهر
            $leaves = LeaveRequest::where('status', 'approved')
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('start_date', '<', $startOfMonth)
                                ->where('end_date', '>', $endOfMonth);
                        });
                })->with('staff')->get();

            $days = [];
            $currentDay = $startOfMonth->copy()->startOfWeek(Carbon::FRIDAY);
            $lastDay = $endOfMonth->copy()->endOfWeek(Carbon::FRIDAY);
            while ($currentDay->lte($lastDay)) {
                $date = $currentDay->copy();
                $dayLeaves = $leaves->filter(function ($leave) use ($date) {
                    return $date->between($leave->start_date, $leave->end_date);
                });

                $days[] = [
                    'date' => $date,
                    'isCurrentMonth' => $date->month === $calendarDate->month, // يعمل على تطبيق التقويم يعني يطابق ال
                    'isToday' => $date->isToday(),
                    'leaves' => $dayLeaves,
                ];

                $currentDay->addDay();
            }

            return $days;
        });
    }

    /**
     * جلب جميع التنبيهات النشطة مرتبة حسب الخطورة
     * تجميع كل التنبيهات المختلفة في مصدر واحد، لتسهيل التعامل معها
     */
    public function getActiveAlerts(): Collection
    {
        return collect()
            ->merge($this->getAbsenceCrisisAlerts())
            ->merge($this->getPendingRequestsAlerts())
            ->merge($this->getBalanceOverdraftAlerts())
            ->sortByDesc('severity')
            ->values();
    }

    /**
     * تنبيهات أزمة الغياب (3+ معلمين في نفس اليوم)
     */
    protected function getAbsenceCrisisAlerts(): Collection
    {
        $alerts = collect();

        $startDate = now()->startOfDay();
        $endDate = now()->addDays(7)->endOfDay();

        // استخدام البيانات المركزية
        $approvedLeaves = $this->getWeekApprovedLeaves();

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();

            $leavesOnDay = $approvedLeaves->filter(function ($leave) use ($currentDate) {
                return $currentDate->between($leave->start_date, $leave->end_date);
            });

            // استخدام العلاقة المحملة مسبقاً بدلاً من isTeacher()
            $teachersOnLeave = $leavesOnDay->filter(function ($leave) {
                return $leave->staff && $leave->staff->teacher !== null;
            });

            if ($teachersOnLeave->count() >= 3) {
                $alerts->push([
                    'id' => 'absence_crisis_' . $dateStr,
                    'type' => 'absence_crisis',
                    'severity' => $teachersOnLeave->count() >= 5 ? 'critical' : 'danger',
                    'title' => $teachersOnLeave->count() . ' معلمين في إجازة يوم ' . $currentDate->translatedFormat('l j F'),
                    'description' => 'قد تتأثر بعض الحصص الدراسية',
                    'date' => $dateStr,
                    'count' => $teachersOnLeave->count(),
                    'staff_names' => $teachersOnLeave->pluck('staff.full_name')->take(3)->implode('، '),
                    'action_url' => route('hr.dashboard') . '?date=' . $dateStr,
                    'action_label' => 'عرض التفاصيل',
                ]);
            }

            $currentDate->addDay();
        }

        return $alerts;
    }

    /**
     * تنبيهات الطلبات المعلقة (أكثر من 48 ساعة)
     */
    protected function getPendingRequestsAlerts(): Collection
    {
        $alerts = collect();
        $stats = $this->getPendingStats();

        if ($stats['old'] > 0) {
            $alerts->push([
                'id' => 'pending_requests_old',
                'type' => 'pending_old',
                'severity' => 'warning',
                'title' => $stats['old'] . ' طلبات معلقة منذ أكثر من 48 ساعة',
                'description' => 'يرجى مراجعة الطلبات المعلقة واتخاذ إجراء',
                'count' => $stats['old'],
                'action_url' => route('hr.leave.approvals'),
                'action_label' => 'مراجعة الطلبات',
            ]);
        }

        if ($stats['urgent'] > 0) {
            $alerts->push([
                'id' => 'pending_requests_urgent',
                'type' => 'pending_urgent',
                'severity' => 'danger',
                'title' => $stats['urgent'] . ' طلبات عاجلة تحتاج موافقة فورية',
                'description' => 'هذه الطلبات تبدأ خلال 24 ساعة',
                'count' => $stats['urgent'],
                'action_url' => route('hr.leave.approvals'),
                'action_label' => 'مراجعة الآن',
            ]);
        }

        return $alerts;
    }

    /**
     * تنبيهات تجاوز الرصيد
     */
    protected function getBalanceOverdraftAlerts(): Collection
    {
        return collect();
    }

    /**
     * إحصائيات اللوحة المحسنة - مع Cache
     */
    public function getEnhancedStats(): array
    {
        // Cache for 5 minutes, invalidate on leave request changes
        return Cache::remember(self::CACHE_KEY_STATS, self::CACHE_TTL_SHORT, function () {
            $totalStaff = Staff::count();
            $onLeaveToday = LeaveRequest::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('status', 'approved')
                ->count();

            $pendingStats = $this->getPendingStats();
            $activeAlerts = $this->getActiveAlerts()->count();

            return [
                'total_staff' => $totalStaff,
                'on_leave_today' => $onLeaveToday,
                'on_leave_percentage' => $totalStaff > 0 ? round(($onLeaveToday / $totalStaff) * 100, 1) : 0,
                'pending_requests' => $pendingStats['total'],
                'urgent_requests' => $pendingStats['urgent'],
                'active_alerts' => $activeAlerts,
            ];
        });
    }

    /**
     * خريطة الأسبوع (Heatmap) - يستخدم البيانات المركزية
     */
    public function getWeekHeatmap(): array
    {
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(6)->endOfDay();

        $approvedLeaves = $this->getWeekApprovedLeaves();

        $heatmap = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();

            $leavesCount = $approvedLeaves->filter(function ($leave) use ($currentDate) {
                return $currentDate->between($leave->start_date, $leave->end_date);
            })->count();

            // تحديد مستوى الكثافة (0-4)
            $level = 0;
            if ($leavesCount > 0)
                $level = 1;
            if ($leavesCount > 2)
                $level = 2;
            if ($leavesCount > 5)
                $level = 3;
            if ($leavesCount > 8)
                $level = 4;

            // تحديد النسبة المئوية للارتفاع (بحد أدنى 15% للجمالية)
            $maxDailyLeaves = 10; // رقم تقديري للحد الأقصى
            $heightPercentage = $leavesCount === 0 ? 15 : min(100, max(15, ($leavesCount / $maxDailyLeaves) * 100));

            // تحديد لون العمود بناءً على الكثافة
            $colorClass = match ($level) {
                0 => 'bg-gray-100 dark:bg-gray-700',
                1 => 'bg-green-400',
                2 => 'bg-green-500',
                3 => 'bg-yellow-500',
                4 => 'bg-red-500',
                default => 'bg-gray-100 dark:bg-gray-700'
            };

            $heatmap[] = [
                'date' => $dateStr,
                'day_name' => $currentDate->translatedFormat('D'), // Sat, Sun...
                'count' => $leavesCount,
                'level' => $level,
                'height_percentage' => $heightPercentage,
                'color_class' => $colorClass,
                'is_today' => $currentDate->isToday(),
            ];

            $currentDate->addDay();
        }

        return $heatmap;
    }

    /**
     * ملخص الحصص المتأثرة مجمعة حسب المعلم
     */
    public function getAffectedClassesSummary(?Carbon $date = null): array
    {
        $affected = $this->getAffectedClasses($date);

        $byTeacher = $affected->groupBy('teacher_id');

        $summary = [];
        foreach ($byTeacher as $teacherId => $classes) {
            $first = $classes->first();
            $summary[] = [
                'teacher_id' => $teacherId,
                'teacher_name' => $first['teacher_name'],
                'classes_count' => $classes->count(),
                'classes' => $classes->toArray(),
            ];
        }

        return [
            'date' => ($date ?? now())->toDateString(),
            'date_formatted' => ($date ?? now())->translatedFormat('l j F'),
            'total_affected' => $affected->count(),
            'teachers_count' => count($summary),
            'by_teacher' => $summary,
        ];
    }

    /**
     * جلب الطلبات المعلقة للعرض
     */
    public function getPendingRequests(int $limit = 5): Collection
    {
        return LeaveRequest::where('status', 'pending')
            ->with(['staff', 'leaveType'])
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();
    }
}
