<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Exceptions;

use Exception;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;

/**
 * يُطرح عندما يكون الصف مُعيناً بالفعل لقالب آخر في نفس السنة الدراسية
 */
class GradeAlreadyAssignedException extends Exception
{
    protected int $gradeId;
    protected int $existingTemplateId;
    protected ?TimetableTemplate $existingTemplate;

    public function __construct(
        int $gradeId,
        int $existingTemplateId,
        ?TimetableTemplate $existingTemplate = null,
        ?string $message = null
    ) {
        $this->gradeId = $gradeId;
        $this->existingTemplateId = $existingTemplateId;
        $this->existingTemplate = $existingTemplate;

        if (!$message) {
            $templateName = $existingTemplate?->name ?? "قالب #{$existingTemplateId}";
            $message = "الصف مُعين بالفعل لـ \"{$templateName}\". لا يمكن تعيين صف لأكثر من قالب في نفس السنة الدراسية.";
        }

        parent::__construct($message);
    }

    public function getGradeId(): int
    {
        return $this->gradeId;
    }

    public function getExistingTemplateId(): int
    {
        return $this->existingTemplateId;
    }

    public function getExistingTemplate(): ?TimetableTemplate
    {
        return $this->existingTemplate;
    }
}
