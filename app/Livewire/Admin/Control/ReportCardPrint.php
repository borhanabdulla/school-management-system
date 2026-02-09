<?php

namespace App\Livewire\Admin\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\Reporting\Services\ReportCardBuilder;
use Livewire\Component;

class ReportCardPrint extends Component
{
    public $sessionId;
    public $studentId; // Optional: if printing for single student
    public $classSectionId; // Optional: if printing for a specific class

    private ReportCardBuilder $builder;

    protected ?\App\Domains\Academic\Student\Services\StudentLookupService $studentLookup = null;

    protected function lookup(): \App\Domains\Academic\Student\Services\StudentLookupService
    {
        return $this->studentLookup ??= app(\App\Domains\Academic\Student\Services\StudentLookupService::class);
    }

    public function mount($sessionId, $studentId = null)
    {
        $this->sessionId = $sessionId;
        $this->studentId = $studentId;
        $this->builder = app(ReportCardBuilder::class);

        // Check query param for class filtering
        if (request()->has('class_section_id') && request()->query('class_section_id')) {
            $this->classSectionId = request()->query('class_section_id');
        }

        // If studentId is provided, we can get classSectionId from it for context if needed
        if ($studentId) {
            $student = $this->lookup()->findForShow($studentId);
            if ($student) {
                $this->classSectionId = $student->current_class_section_id;
            }
        }
    }

    public function getSessionProperty()
    {
        return ExamSession::with(['academicYear', 'term'])->findOrFail($this->sessionId);
    }

    public function getStudentsProperty()
    {
        return $this->lookup()->getStudentsForExamSession(
            sessionId: $this->sessionId,
            studentId: $this->studentId,
            classSectionId: $this->classSectionId
        );
    }

    public function render()
    {
        $students = $this->students;

        $reports = $this->builder->buildForStudents(
            $students,
            $this->session->term->id,
            $this->classSectionId
        );

        $studentScores = collect($reports)->map(fn ($report) => $report['totals']['total_score'] ?? 0)->sortDesc();

        $ranks = [];
        $rank = 1;
        $prevScore = null;
        $count = 0;

        foreach ($studentScores as $studentId => $score) {
            $count++;
            if ($prevScore !== null && $score < $prevScore) {
                $rank = $count;
            }
            $ranks[$studentId] = $rank;
            $prevScore = $score;
        }

        return view('livewire.admin.control.report-card-print', [
            'students' => $students,
            'reports' => $reports,
            'ranks' => $ranks,
        ])->layout('layouts.print');
    }
}
