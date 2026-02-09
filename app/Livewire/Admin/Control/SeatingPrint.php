<?php

namespace App\Livewire\Admin\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Control\Models\ExamSeating;
use Livewire\Component;

class SeatingPrint extends Component
{
    public $sessionId;
    public $printType = 'seating'; // seating, secret, both

    public function mount($sessionId, $type = 'seating')
    {
        $this->sessionId = $sessionId;
        $this->printType = $type;
    }

    public function getSessionProperty()
    {
        return ExamSession::with(['academicYear', 'term'])->find($this->sessionId);
    }

    public function getSeatingsProperty()
    {
        return ExamSeating::where('exam_session_id', $this->sessionId)
            ->with(['student.currentClassSection.grade'])
            ->orderBy('seat_number')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.control.seating-print')
            ->layout('layouts.print');
    }
}
