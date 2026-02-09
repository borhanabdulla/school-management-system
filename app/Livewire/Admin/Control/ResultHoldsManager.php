<?php

namespace App\Livewire\Admin\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Models\ExamSeating;
use Livewire\Component;
use Livewire\WithPagination;

class ResultHoldsManager extends Component
{
    use WithPagination;

    public $sessionId;
    public $search = '';

    // For editing
    public $editingSeatingId;
    public $withholdReason;

    public function mount($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function getSessionProperty()
    {
        return ExamSession::findOrFail($this->sessionId);
    }

    public function toggleWithhold($seatingId)
    {
        $seating = $this->resolveSeating((int) $seatingId);
        $seating->is_withheld = !$seating->is_withheld;
        if (!$seating->is_withheld) {
            $seating->withhold_reason = null;
        }
        $seating->save();
    }

    public function editReason($seatingId)
    {
        $this->editingSeatingId = $seatingId;
        $seating = $this->resolveSeating((int) $seatingId);
        $this->withholdReason = $seating->withhold_reason;
    }

    public function saveReason()
    {
        if (! $this->editingSeatingId) {
            return;
        }

        $seating = $this->resolveSeating((int) $this->editingSeatingId);
        $seating->withhold_reason = $this->withholdReason;
        $seating->save();
        $this->editingSeatingId = null;
    }

    public function cancelEdit()
    {
        $this->editingSeatingId = null;
    }

    public function render()
    {
        $seatings = ExamSeating::where('exam_session_id', $this->sessionId)
            ->whereHas('student', function ($q) {
                $q->search($this->search);
            })
            ->with(['student.currentClassSection.grade'])
            ->orderBy('seat_number')
            ->paginate(20);

        return view('livewire.admin.control.result-holds-manager', [
            'seatings' => $seatings
        ])->layout('layouts.app');
    }

    private function resolveSeating(int $seatingId): ExamSeating
    {
        return ExamSeating::where('id', $seatingId)
            ->where('exam_session_id', $this->sessionId)
            ->firstOrFail();
    }
}
