<?php

namespace App\Livewire\Admin\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Control\Services\BlindEntryService;
use Livewire\Component;

class BlindGrading extends Component
{
    public $sessionId;
    public $courseOfferingId;

    // Entry fields
    public $secretNumber = '';
    public $score = '';
    public $isAbsent = false;

    // Feedback
    public $lastEntry = null;
    public $lastError = null;
    public $audioFeedback = null; // 'success' or 'error'

    public function mount($sessionId = null)
    {
        if ($sessionId) {
            $this->sessionId = $sessionId;
        } else {
            // استخدام الدورة النشطة تلقائياً
            $active = ExamSession::where('is_active', true)->first();
            $this->sessionId = $active?->id;
        }
    }

    public function getSessionProperty()
    {
        return ExamSession::find($this->sessionId);
    }

    public function getSubjectsProperty()
    {
        if (!$this->session) {
            return collect();
        }

        return CourseOffering::where('term_id', $this->session->term_id)
            ->with(['subject', 'classSection.grade'])
            ->get()
            ->sortBy('subject.name');
    }

    public function getStatsProperty()
    {
        if (!$this->session || !$this->courseOfferingId) {
            return null;
        }

        $service = app(BlindEntryService::class);
        return $service->getSubjectStats($this->session, $this->courseOfferingId);
    }

    public function submitGrade(BlindEntryService $service)
    {
        $this->lastEntry = null;
        $this->lastError = null;
        $this->audioFeedback = null;

        if (!$this->session) {
            $this->lastError = 'لا توجد دورة امتحانية نشطة.';
            $this->audioFeedback = 'error';
            return;
        }

        if (!$this->courseOfferingId) {
            $this->lastError = 'يرجى اختيار المادة أولاً.';
            $this->audioFeedback = 'error';
            return;
        }

        if (empty($this->secretNumber)) {
            $this->lastError = 'يرجى إدخال الرقم السري.';
            $this->audioFeedback = 'error';
            return;
        }

        if (!$this->isAbsent && ($this->score === '' || $this->score === null)) {
            $this->lastError = 'يرجى إدخال الدرجة أو تحديد غياب.';
            $this->audioFeedback = 'error';
            return;
        }

        try {
            $mark = $service->submitGrade(
                $this->session,
                strtoupper(trim($this->secretNumber)),
                $this->courseOfferingId,
                $this->isAbsent ? null : (float) $this->score,
                $this->isAbsent
            );

            $this->lastEntry = [
                'secret' => $this->secretNumber,
                'score' => $this->isAbsent ? 'غائب' : $this->score,
            ];
            $this->audioFeedback = 'success';

            // مسح الحقول للإدخال التالي
            $this->reset(['secretNumber', 'score', 'isAbsent']);

        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            $this->audioFeedback = 'error';
        }
    }

    public function validateSecret(BlindEntryService $service)
    {
        if (empty($this->secretNumber)) {
            return;
        }

        $seating = $service->validateSecretNumber(
            $this->session,
            strtoupper(trim($this->secretNumber))
        );

        if (!$seating) {
            $this->lastError = "الرقم السري غير موجود.";
            $this->audioFeedback = 'error';
        } else {
            $this->lastError = null;
        }
    }

    public function render()
    {
        return view('livewire.admin.control.blind-grading')
            ->layout('layouts.app');
    }
}
