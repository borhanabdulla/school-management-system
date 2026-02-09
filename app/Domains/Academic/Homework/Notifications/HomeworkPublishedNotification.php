<?php

namespace App\Domains\Academic\Homework\Notifications;

use App\Domains\Academic\Homework\Models\Homework;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * HomeworkPublishedNotification - إشعار نشر واجب جديد
 */
class HomeworkPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Homework $homework
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'واجب جديد: ' . $this->homework->title,
            'message' => 'تم نشر واجب جديد في مادة ' . $this->homework->courseOffering->subject->name,
            'link' => route('student.homeworks.index'),
            'type' => 'homework',
            'icon' => 'book',
            'color' => 'indigo',
        ];
    }
}
