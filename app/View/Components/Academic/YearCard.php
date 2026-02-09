<?php

namespace App\View\Components\Academic;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\View\Component;
use App\Infrastructure\Support\Helpers;

class YearCard extends Component
{
    public AcademicYear $year;
    public string $statusValue;
    public int $progress;
    public int $daysRemaining;
    public array $theme;

    public function __construct(AcademicYear $year)
    {
        $this->year = $year;
        $this->statusValue = $year->status->value;

        $this->calculateProgress();
        $this->setTheme();
    }

    protected function calculateProgress(): void
    {
        $totalDays = $this->year->start_date->diffInDays($this->year->end_date);
        $daysPassed = $this->year->start_date->diffInDays(now());

        if (now()->lt($this->year->start_date)) {
            $this->progress = 0;
        } elseif (now()->gt($this->year->end_date)) {
            $this->progress = 100;
        } else {
            $this->progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
        }

        $this->daysRemaining = (int) now()->diffInDays($this->year->end_date, false);
    }

    protected function setTheme(): void
    {
        $color = $this->year->status->color(); // green, yellow, red, gray

        $this->theme = [
            'border_top' => "bg-{$color}-500",
            'title_hover' => "group-hover:text-{$color}-600",
            'badge' => $this->year->status->styles(),
            'dot' => "bg-{$color}-500",
            'icon_bg' => "from-{$color}-500 to-{$color}-600",
            'icon_text' => "text-{$color}-500",
            'progress_bar' => "bg-{$color}-500",
            'hover_border' => "hover:border-{$color}-200",
            'progress_text' => "text-{$color}-600",
        ];
    }

    public function render()
    {
        return view('components.academic.year-card');
    }
}
