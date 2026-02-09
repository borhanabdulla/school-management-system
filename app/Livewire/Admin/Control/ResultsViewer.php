<?php

namespace App\Livewire\Admin\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Services\FinalResultsViewerService;
use Livewire\Component;

class ResultsViewer extends Component
{
    public $sessionId;
    public $courseOfferingId;
    public $statusFilter = 'all'; // all, pass, fail

    private FinalResultsViewerService $viewer;

    public function mount($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->viewer = app(FinalResultsViewerService::class);
    }

    public function getSessionProperty()
    {
        return ExamSession::with(['academicYear', 'term'])->find($this->sessionId);
    }

    public function getSubjectsProperty()
    {
        if (!$this->session) {
            return collect();
        }

        return $this->viewer->subjects($this->session);
    }

    public function getResultsProperty()
    {
        if (! $this->session) {
            return collect();
        }

        return $this->viewer->results(
            $this->session,
            $this->courseOfferingId,
            $this->statusFilter
        );
    }

    public function getStatsProperty()
    {
        if (! $this->session) {
            return [
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'absent' => 0,
                'pass_rate' => 0,
                'average' => 0,
            ];
        }

        return $this->viewer->stats($this->session, $this->courseOfferingId);
    }

    public function render()
    {
        return view('livewire.admin.control.results-viewer')
            ->layout('layouts.app');
    }
}
