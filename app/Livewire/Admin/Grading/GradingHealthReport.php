<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Grading;

use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use App\Domains\Academic\Term\Models\Term;
use Livewire\Attributes\On;
use Livewire\Component;

final class GradingHealthReport extends Component
{
    public ?int $termId = null;

    public array $terms = [];

    public array $report = [
        'checked' => 0,
        'missing' => [],
        'invalid' => [],
        'warnings' => [],
    ];

    public bool $isChecking = false;

    public string $statusMessage = '';

    private GradingConfigHealthChecker $checker;

    public function mount(GradingConfigHealthChecker $checker): void
    {
        $this->checker = $checker;
        $this->terms = Term::orderByDesc('start_date')
            ->get(['id', 'name'])
            ->map(fn (Term $term) => [
                'id' => $term->id,
                'name' => $term->name,
            ])
            ->toArray();

        $this->termId = $this->termId ?? ($this->terms[0]['id'] ?? null);

        if ($this->termId) {
            $this->refreshReport();
        }
    }

    public function updatedTermId(): void
    {
        $this->refreshReport();
    }

    public function refreshReport(): void
    {
        if (! $this->termId) {
            $this->report = $this->emptyReport();
            $this->statusMessage = 'لم يتم اختيار ترم';
            return;
        }

        $term = Term::find($this->termId);

        if (! $term) {
            $this->report = $this->emptyReport();
            $this->statusMessage = 'الترم غير موجود';
            $this->dispatch('gradingHealthReportUpdated', [
                'clean' => true,
                'missing' => 0,
                'invalid' => 0,
            ]);
            return;
        }

        $this->isChecking = true;
        $this->statusMessage = 'جاري الفحص...';

        try {
            $healthReport = $this->checker->checkTerm($term);
            $this->report = $healthReport->toArray();
            $this->statusMessage = $this->isClean
                ? 'التكوينات نظيفة، يمكنك المتابعة'
                : 'يوجد قضايا تحتاج إصلاح قبل الحساب';
            $this->dispatch('gradingHealthReportUpdated', [
                'clean' => $this->isClean,
                'missing' => count($this->report['missing']),
                'invalid' => count($this->report['invalid']),
            ]);
        } catch (\Throwable $exception) {
            logger()->error('Livewire grading health check failed', ['exception' => $exception]);
            $this->statusMessage = 'فشل الفحص، راجع اللوغ لمزيد من المعلومات';
            $this->dispatch('gradingHealthReportUpdated', [
                'clean' => false,
                'missing' => count($this->report['missing']),
                'invalid' => count($this->report['invalid']),
            ]);
        } finally {
            $this->isChecking = false;
        }
    }

    #[On('runGradingHealthCheckRequested')]
    public function refreshOnRequest(): void
    {
        $this->refreshReport();
    }

    public function getIsCleanProperty(): bool
    {
        return empty($this->report['missing']) && empty($this->report['invalid']);
    }

    private function emptyReport(): array
    {
        return [
            'checked' => 0,
            'missing' => [],
            'invalid' => [],
            'warnings' => [],
        ];
    }

    public function render()
    {
        return view('livewire.admin.grading.grading-health-report');
    }
}
