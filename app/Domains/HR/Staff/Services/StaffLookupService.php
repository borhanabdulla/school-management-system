<?php

namespace App\Domains\HR\Staff\Services;

use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * StaffLookupService - خدمة البحث عن الموظفين
 */
class StaffLookupService
{
    /**
     * جلب قائمة الموظفين مع الفلترة
     */
    public function getStaffList(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Staff::query()
            ->with(['user', 'workShift'])

            // 1. البحث العام
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })

            // 2. فلتر القسم/الوظيفة (إذا كان متاحاً في الموديل)
            ->when($filters['job_title'] ?? null, function (Builder $query, $jobTitle) {
                $query->where('job_title', 'like', "%{$jobTitle}%");
            })

            // 3. فلتر الحالة
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })

            ->latest('joining_date')
            ->paginate($perPage);
    }

    /**
     * بحث سريع للقوائم المنسدلة (مع كاش)
     */
    public function searchStaffForDropdown(string $query = ''): array
    {
        $cacheKey = 'staff_search_' . md5($query);

        return Cache::remember($cacheKey, 300, function () use ($query) {
            return Staff::query()
                ->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('employee_number', 'like', "%{$query}%")
                ->limit(20)
                ->get()
                ->map(function ($staff) {
                    return [
                        'id' => $staff->id,
                        'name' => $staff->full_name . ' (' . $staff->employee_number . ')',
                    ];
                })
                ->toArray();
        });
    }
}
