<?php

namespace App\Livewire\Teacher\Homework;

use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use Livewire\Component;

class HomeworkGrader extends Component
{
    public $homeworkId;
    public $grades = []; // [submission_id => score]
    public $feedbacks = []; // [submission_id => feedback]

    public function mount($homeworkId)
    {
        $this->homeworkId = $homeworkId;
        $this->loadGrades();
    }

    public function getHomeworkProperty()
    {
        return Homework::with(['courseOffering.classSection', 'submissions.student'])->findOrFail($this->homeworkId);
    }

    public function loadGrades()
    {
        $this->grades = [];
        $this->feedbacks = [];

        foreach ($this->homework->submissions as $submission) {
            $this->grades[$submission->id] = $submission->score;
            $this->feedbacks[$submission->id] = $submission->feedback;
        }
    }

    public function updateGrade($submissionId)
    {
        $submission = HomeworkSubmission::findOrFail($submissionId);

        $score = $this->grades[$submissionId] ?? null;
        $feedback = $this->feedbacks[$submissionId] ?? null;

        if ($score !== null && $score !== '') {
            $submission->update([
                'score' => $score,
                'feedback' => $feedback,
                'status' => SubmissionStatus::GRADED,
            ]);

            // Observer will handle GradeSyncService

            $this->dispatch('toast', message: 'تم رصد الدرجة للطالب ' . $submission->student->full_name);
        }
    }

    public function markAllFull()
    {
        foreach ($this->homework->submissions as $submission) {
            $this->grades[$submission->id] = $this->homework->max_score;
            $this->updateGrade($submission->id);
        }

        $this->dispatch('toast', message: 'تم رصد الدرجة الكاملة للجميع.');
    }


    public function render()
    {
        return view('livewire.teacher.homework.homework-grader')
            ->layout('layouts.app');
    }
}
