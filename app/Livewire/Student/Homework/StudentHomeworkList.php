<?php

namespace App\Livewire\Student\Homework;

use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentHomeworkList extends Component
{
    use WithFileUploads;

    public $filter = 'all'; // all, pending, submitted
    public $submissionFile;
    public $selectedSubmissionId;
    public $showUploadModal = false;

    public function getSubmissionsProperty()
    {
        // Auth::id() returns User ID, but homework_submissions.student_id is Student ID
        $studentId = auth()->user()?->student?->id;

        if (!$studentId) {
            return collect(); // Return empty if user is not a student
        }

        $query = HomeworkSubmission::with(['homework.courseOffering.subject'])
            ->where('student_id', $studentId)
            ->orderByDesc('created_at');

        if ($this->filter === 'pending') {
            $query->whereIn('status', [SubmissionStatus::PENDING, SubmissionStatus::LATE]);
        } elseif ($this->filter === 'submitted') {
            $query->whereIn('status', [SubmissionStatus::SUBMITTED, SubmissionStatus::GRADED]);
        }

        return $query->get();
    }

    public function openUploadModal($submissionId)
    {
        $this->selectedSubmissionId = $submissionId;
        $this->showUploadModal = true;
    }

    public function uploadFile()
    {
        $this->validate([
            'submissionFile' => 'required|file|max:10240', // 10MB max
        ]);

        $submission = HomeworkSubmission::findOrFail($this->selectedSubmissionId);

        // Check if allowed to submit
        if ($submission->homework->submission_type !== SubmissionType::ONLINE) {
            $this->addError('submissionFile', 'هذا الواجب لا يقبل التسليم الإلكتروني.');
            return;
        }

        // Check due date logic
        $isLate = false;
        if ($submission->homework->due_date && now()->gt($submission->homework->due_date)) {
            if (!$submission->homework->allow_late) {
                $this->addError('submissionFile', 'انتهى موعد التسليم ولا يُسمح بالتسليم المتأخر.');
                return;
            }
            $isLate = true;
        }

        $path = $this->submissionFile->store('homeworks/' . $submission->homework_id, 'public');

        $submission->update([
            'file_path' => $path,
            'submitted_at' => now(),
            'status' => $isLate ? SubmissionStatus::LATE : SubmissionStatus::SUBMITTED,
        ]);

        $this->showUploadModal = false;
        $this->reset('submissionFile');
        $this->dispatch('notify', message: 'تم تسليم الواجب بنجاح.');
    }

    public function render()
    {
        return view('livewire.student.homework.student-homework-list')
            ->layout('layouts.app');
    }
}
