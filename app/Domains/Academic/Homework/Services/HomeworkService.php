<?php

namespace App\Domains\Academic\Homework\Services;

use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Models\Homework;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class HomeworkService
{
    public function createHomework(array $data): Homework
    {
        return DB::transaction(function () use ($data) {
            // Validate due date if status is published
            if (isset($data['status']) && $data['status'] === HomeworkStatus::PUBLISHED->value) {
                $this->validateDueDate($data['due_date'] ?? null);
            }

            return Homework::create($data);
        });
    }

    public function updateHomework(Homework $homework, array $data): Homework
    {
        return DB::transaction(function () use ($homework, $data) {
            // If status is changing to published, validate due date
            if (
                isset($data['status']) &&
                $data['status'] === HomeworkStatus::PUBLISHED->value &&
                $homework->status !== HomeworkStatus::PUBLISHED
            ) {
                $this->validateDueDate($data['due_date'] ?? $homework->due_date);
            }

            $homework->update($data);
            return $homework;
        });
    }

    public function publishHomework(Homework $homework): Homework
    {
        $this->validateDueDate($homework->due_date);

        $homework->update(['status' => HomeworkStatus::PUBLISHED]);

        return $homework;
    }

    public function deleteHomework(Homework $homework): void
    {
        DB::transaction(function () use ($homework) {
            // Logic to handle grade recalculation will be in Observers or GradeSyncService
            // triggered by deletion if necessary.
            // For now, simple deletion.
            $homework->delete();
        });
    }

    protected function validateDueDate($dueDate): void
    {
        if (!$dueDate) {
            throw new Exception("تاريخ الاستحقاق مطلوب للنشر.");
        }

        if (Carbon::parse($dueDate)->isPast()) {
            throw new Exception("لا يمكن نشر واجب بتاريخ استحقاق في الماضي.");
        }
    }
}
