<?php

namespace App\Domains\Academic\Attendance\Listeners;

use App\Events\StudentAttendanceSaved;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * SendAttendanceNotifications - إرسال إشعارات الحضور
 * 
 * يرسل إشعارات لأولياء الأمور عند تسجيل حضور/غياب الطالب
 */
class SendAttendanceNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
    }

    /**
     * معالجة الحدث
     */
    public function handle(StudentAttendanceSaved $event): void
    {
        $students = app(\App\Domains\Academic\Student\Services\StudentLookupService::class)
            ->findManyWithGuardians($event->absentStudentIds);

        foreach ($students as $student) {
            foreach ($student->guardians as $guardian) {
                if ($guardian->user) {
                    // إرسال إشعار للمستخدم
                    // $guardian->user->notify(new StudentAbsentNotification(...));
                }
            }
        }
    }
}
