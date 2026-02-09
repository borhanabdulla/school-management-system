<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\HR\Staff\Enums\StaffStatus;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;
use Illuminate\Support\Facades\DB;

class PeopleDashboardService
{
    use HasDashboardCache;

    public function staffBreakdown(): array
    {
        return $this->rememberDashboard([], 'staff_breakdown', 10, function () {
            $counts = Staff::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $items = [];
            foreach (StaffStatus::cases() as $status) {
                $items[] = [
                    'label' => $status->label(),
                    'total' => (int) ($counts[$status->value] ?? 0),
                    'color' => $status->color(),
                ];
            }

            $max = !empty($items) ? max(array_column($items, 'total')) : 0;

            return [
                'items' => $items,
                'max' => $max > 0 ? $max : 1,
            ];
        }, includeFilters: false);
    }

    public function topTeachers(): array
    {
        return $this->rememberDashboard([], 'top_teachers', 10, function () {
            $teachers = Teacher::query()
                ->with('staff')
                ->withCount('courseOfferings')
                ->orderByDesc('course_offerings_count')
                ->limit(5)
                ->get();

            $items = $teachers->map(function ($teacher) {
                return [
                    'name' => $teacher->staff?->full_name ?? 'غير محدد',
                    'count' => (int) $teacher->course_offerings_count,
                ];
            })->toArray();

            $max = !empty($items) ? max(array_column($items, 'count')) : 0;

            return [
                'items' => $items,
                'max' => $max > 0 ? $max : 1,
            ];
        }, includeFilters: false);
    }
}
