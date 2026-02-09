<?php

namespace App\Domains\HR\Staff\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * StaffWelcomeNotification - إشعار ترحيبي للموظف الجديد
 */
class StaffWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $username,
        public string $password
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('مرحباً بك في نظام المدرسة الذكية')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم إنشاء حسابك بنجاح في نظام إدارة المدرسة.')
            ->line('بيانات الدخول الخاصة بك:')
            ->line('**البريد الإلكتروني:** ' . $this->username)
            ->line('**كلمة المرور:** ' . $this->password)
            ->action('تسجيل الدخول', url('/login'))
            ->line('⚠️ يرجى تغيير كلمة المرور فور تسجيل الدخول لأول مرة.')
            ->salutation('مع تحيات فريق نظام المدرسة الذكية');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_welcome',
            'username' => $this->username,
        ];
    }
}
