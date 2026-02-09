<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;

class EventsDashboardService
{
    use HasDashboardCache;

    public function upcomingEvents(array $filters): array
    {
        return $this->rememberDashboard($filters, 'upcoming_events', 5, function () use ($filters) {
            $query = SchoolEvent::query()
                ->whereDate('start_date', '>=', today())
                ->orderBy('start_date');

            $academicYearId = $filters['academicYearId'] ?? null;
            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            } else {
                $query->currentYear();
            }

            return $query->limit(5)->get()->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start' => $event->start_date?->format('Y/m/d'),
                    'end' => $event->end_date?->format('Y/m/d'),
                    'type' => $event->type_name,
                    'color' => $event->type_color,
                    'is_holiday' => $event->is_holiday,
                ];
            })->toArray();
        });
    }
}
