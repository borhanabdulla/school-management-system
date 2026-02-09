<?php

namespace App\Domains\Academic\Student\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAbsentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $studentName;
    public $subjectName;
    public $date;
    public $timeSlot;

    /**
     * Create a new notification instance.
     */
    public function __construct($studentName, $subjectName, $date, $timeSlot)
    {
        $this->studentName = $studentName;
        $this->subjectName = $subjectName;
        $this->date = $date;
        $this->timeSlot = $timeSlot;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // ديناميكية القنوات: يمكن جلبها من إعدادات المستخدم لاحقاً
        // حالياً سنفعل قاعدة البيانات والبريد
        // return ['database', 'mail'];
        return ['database']; // سنكتفي بقاعدة البيانات للتجربة السريعة
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تنبيه غياب: ' . $this->studentName)
            ->greeting('مرحباً ولي أمر الطالب ' . $this->studentName)
            ->line("نحيطكم علماً بأن الطالب تغيب عن حصة {$this->subjectName} بتاريخ {$this->date}.")
            ->line('نرجو المتابعة والاهتمام.')
            ->action('عرض التفاصيل', url('/'))
            ->line('شكراً لتعاونكم.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تنبيه غياب',
            'body' => "تغيب الطالب {$this->studentName} عن حصة {$this->subjectName}.",
            'student_name' => $this->studentName,
            'subject' => $this->subjectName,
            'date' => $this->date,
            'time_slot' => $this->timeSlot,
        ];
    }
}
