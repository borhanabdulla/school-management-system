<?php

namespace App\Domains\Academic\Attendance\Listeners;

use App\Events\StudentAttendanceSaved;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * SendAbsentNotification - إرسال إشعارات الغياب
 * 
 * يرسل إشعارات لأولياء الأمور عند تسجيل غياب الطالب
 */
class SendAbsentNotification implements ShouldQueue
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
        if (empty($event->absentStudentIds)) {
            return;
        }

        $students = app(\App\Domains\Academic\Student\Services\StudentLookupService::class)
            ->findManyWithGuardians($event->absentStudentIds);

        foreach ($students as $student) {
            foreach ($student->guardians as $guardian) {
                $message = "Notification to Guardian: {$guardian->full_name} ({$guardian->email}) - " .
                    "Student {$student->full_name_ar} was marked ABSENT on {$event->date} " .
                    "for class {$event->timetable->courseOffering->subject->name}.";

                Log::info($message);
            }
        }
    }
}
