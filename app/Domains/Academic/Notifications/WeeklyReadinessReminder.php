<?php

namespace App\Domains\Academic\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Domains\Academic\Data\ReadinessItem;
use Illuminate\Support\Collection;

/**
 * Notification sent to remind about readiness issues
 *
 * This notification is sent weekly to teachers/admins
 * to remind them of pending tasks before year closing.
 */
class WeeklyReadinessReminder extends Notification
{
    /**
     * The readiness items with issues
     */
    protected Collection $items;

    /**
     * The academic year name
     */
    protected string $yearName;

    /**
     * The year ID for route params
     */
    protected int $yearId;

    /**
     * Create a new notification instance
     */
    public function __construct(
        Collection $items,
        string $yearName,
        int $yearId
    ) {
        $this->items = $items;
        $this->yearName = $yearName;
        $this->yearId = $yearId;
    }

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('تذكير أسبوعي: جاهزية إغلاق السنة الدراسية - ' . $this->yearName)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('هذا تذكير أسبوعي بآخر مستجدات جاهزية إغلاق السنة الدراسية.');

        // Add blocking items section
        $blockingItems = $this->items->filter(fn(ReadinessItem $item) => $item->isBlocking());

        if ($blockingItems->isNotEmpty()) {
            $mail->line('')
                ->error('⚠️ مشاكل تمنع الإغلاق:');

            foreach ($blockingItems as $item) {
                $mail->line("- {$item->label}: {$item->message}");
            }
        }

        // Add warning items section
        $warningItems = $this->items->filter(fn(ReadinessItem $item) => $item->isWarning());

        if ($warningItems->isNotEmpty()) {
            $mail->line('')
                ->line('⚡ مهام مطلوبة:');

            foreach ($warningItems as $item) {
                $mail->line("- {$item->label}: {$item->message}");
            }
        }

        // Add action button
        $mail->action('عرض تفاصيل الجاهزية', route('academic-years.readiness', $this->yearId));

        return $mail
            ->line('')
            ->line('مع تحيات،')
            ->line('نظام إدارة المدارس');
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'year_id' => $this->yearId,
            'year_name' => $this->yearName,
            'blocking_count' => $this->items->filter(fn($item) => $item->isBlocking())->count(),
            'warning_count' => $this->items->filter(fn($item) => $item->isWarning())->count(),
            'items' => $this->items->map(fn($item) => [
                'key' => $item->key,
                'severity' => $item->severity->value,
                'label' => $item->label,
                'message' => $item->message,
                'count' => $item->count,
            ])->toArray(),
            'type' => 'weekly_readiness_reminder',
        ];
    }
}
