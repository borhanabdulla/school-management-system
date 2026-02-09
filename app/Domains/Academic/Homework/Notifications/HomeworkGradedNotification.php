<?php

namespace App\Domains\Academic\Homework\Notifications;

use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * HomeworkGradedNotification - إشعار رصد درجة الواجب
 */
class HomeworkGradedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public HomeworkSubmission $submission
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم رصد درجة الواجب: ' . $this->submission->homework->title,
            'message' => 'حصلت على ' . $this->submission->score . ' من ' . $this->submission->homework->max_score,
            'link' => route('student.homeworks.index'),
            'type' => 'grade',
            'icon' => 'check-circle',
            'color' => 'green',
        ];
    }
}
