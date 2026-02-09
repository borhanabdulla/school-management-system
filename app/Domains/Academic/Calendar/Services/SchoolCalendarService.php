<?php

namespace App\Domains\Academic\Calendar\Services;

use App\Domains\Academic\Calendar\Models\SchoolEvent;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SchoolCalendarService
{
    private const CACHE_KEY_EVENTS = 'school_events_current_year';
    private const CACHE_KEY_WEEKEND_DAYS = 'school_weekend_days';

    /**
     * ✅ Performance Optimization: Instance-level cache for weekend_days
     * Cached per active year ID to avoid repeated json_decode in loops
     */
    private ?array $cachedWeekendDays = null;
    private ?int $cachedYearId = null;

    public function __construct()
    {
    }

    /**
     * جلب أحداث السنة الحالية (مع الكاش)
     */
    public function getEventsForCurrentYear(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::CACHE_KEY_EVENTS, 60 * 60 * 24, function () {
            return SchoolEvent::currentYear()->get();
        });
    }

    /**
     * جلب أحداث لشهر معين
     */
    public function getEventsForMonth(int $year, int $month): \Illuminate\Database\Eloquent\Collection
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        return SchoolEvent::currentYear()
            ->inRange($startOfMonth, $endOfMonth)
            ->orderBy('start_date')
            ->get();
    }

    /**
     * ✅ PR1.1: Get weekend_days configuration from active Academic Year
     * Strict: Uses fallback if not configured (no hard errors)
     * 
     * ✅ Performance: Uses instance-level cache to avoid repeated json_decode in loops
     * (e.g., when generating monthly attendance reports for multiple students)
     * 
     * @return array<int> Array of day-of-week numbers (0=Sunday, 6=Saturday)
     */
    public function getWeekendDays(): array
    {
        $activeYear = app(\App\Infrastructure\Context\AcademicContextService::class)->activeYear();

        if (!$activeYear || !$activeYear->weekend_days) {
            // ✅ Fallback to default [0, 6] if no active year or config missing
            return [0, 6];
        }

        // ✅ Performance: Return cached value if same year
        if ($this->cachedWeekendDays !== null && $this->cachedYearId === $activeYear->id) {
            return $this->cachedWeekendDays;
        }

        $weekendDays = json_decode($activeYear->weekend_days, true);

        if (!is_array($weekendDays) || empty($weekendDays)) {
            // ✅ Fallback if invalid JSON
            return [0, 6];
        }

        // ✅ Performance: Cache the decoded value
        $this->cachedWeekendDays = $weekendDays;
        $this->cachedYearId = $activeYear->id;

        return $weekendDays;
    }

    /**
     * ✅ PR1.1: Check if a date falls on a weekend (based on configured weekend_days)
     * 
     * @param string|\DateTimeInterface|Carbon $date
     * @return bool
     */
    public function isWeekend($date): bool
    {
        $date = Carbon::parse($date)->startOfDay();
        $weekendDays = $this->getWeekendDays();
        return in_array($date->dayOfWeek, $weekendDays, true);
    }

    /**
     * هل هذا التاريخ عطلة؟
     * ✅ PR1.1: Uses unified weekend check via isWeekend()
     */
    public function isHoliday($date): bool
    {
        $date = Carbon::parse($date)->startOfDay();

        // ✅ PR1.1: Use unified weekend check (no duplicate logic)
        if ($this->isWeekend($date)) {
            return true;
        }

        // Check events from cache to avoid N+1 queries
        $events = $this->getEventsForCurrentYear();

        return $events->where('is_holiday', true)
            ->filter(function ($event) use ($date) {
                $start = Carbon::parse($event->start_date)->startOfDay();
                $end = Carbon::parse($event->end_date)->startOfDay();
                return $date->between($start, $end);
            })->isNotEmpty();
    }

    /**
     * جلب العطل القادمة
     */
    public function getUpcomingHolidays(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return SchoolEvent::currentYear()
            ->holidays()
            ->where('start_date', '>=', Carbon::today())
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    /**
     * ✅ PR-4: الحصول على أيام نهاية الأسبوع لسنة محددة
     *
     * @param int $yearId معرف السنة الدراسية
     * @return array<int> Array of day-of-week numbers (0=Sunday, 6=Saturday)
     */
    public function getWeekendDaysForYear(int $yearId): array
    {
        $year = \App\Domains\Academic\AcademicYear\Models\AcademicYear::find($yearId);
        
        if (!$year || !$year->weekend_days) {
            // Fallback to default [0, 6] if year not found or config missing
            return [0, 6];
        }
        
        $weekendDays = json_decode($year->weekend_days, true);
        return is_array($weekendDays) && !empty($weekendDays) ? $weekendDays : [0, 6];
    }

    /**
     * ✅ PR-4: الحصول على أحداث لسنة محددة
     *
     * @param int $yearId معرف السنة الدراسية
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEventsForYear(int $yearId): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("school_events_year_{$yearId}", 3600, function () use ($yearId) {
            return \App\Domains\Academic\Calendar\Models\SchoolEvent::forYear($yearId)->get();
        });
    }

    /**
     * ✅ PR-4: التحقق من عطلة لسنة محددة (Range-aware)
     *
     * @param string|\DateTimeInterface|Carbon $date
     * @param int $yearId معرف السنة الدراسية
     * @return bool
     */
    public function isHolidayForYear($date, int $yearId): bool
    {
        $date = Carbon::parse($date)->startOfDay();
        
        // 1. التحقق من weekend days
        $weekendDays = $this->getWeekendDaysForYear($yearId);
        if (in_array($date->dayOfWeek, $weekendDays, true)) {
            return true;
        }
        
        // 2. التحقق من holidays (Range-aware)
        $events = $this->getEventsForYear($yearId);
        
        foreach ($events as $event) {
            // تحقق من نوع الحدث (عمود is_holiday)
            if ($event->is_holiday) {
                $start = Carbon::parse($event->start_date)->startOfDay();
                $end = Carbon::parse($event->end_date)->endOfDay();
                
                // ✅ PR-4: Range check شامل (inclusive)
                if ($date->gte($start) && $date->lte($end)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * مسح الكاش (يُستدعى من Observer)
     * ✅ Performance: Also clears weekend_days cache when academic year changes
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_EVENTS);
        Cache::forget(self::CACHE_KEY_WEEKEND_DAYS);
        
        // ✅ Performance: Clear instance-level cache
        $this->cachedWeekendDays = null;
        $this->cachedYearId = null;
    }

    /**
     * حساب أيام الدراسة الفعلية بين تاريخين
     */
    public function countSchoolDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $count = 0;

        while ($start <= $end) {
            if (!$this->isHoliday($start)) {
                $count++;
            }
            $start->addDay();
        }

        return $count;
    }
}
