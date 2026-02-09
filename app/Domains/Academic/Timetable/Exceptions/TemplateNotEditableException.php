<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Exceptions;

use Exception;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;

/**
 * يُطرح عند محاولة تعديل قالب غير قابل للتعديل
 */
class TemplateNotEditableException extends Exception
{
    protected int $templateId;
    protected TemplateStatus $currentStatus;

    public function __construct(
        int $templateId,
        TemplateStatus $currentStatus,
        ?string $message = null
    ) {
        $this->templateId = $templateId;
        $this->currentStatus = $currentStatus;

        if (!$message) {
            $message = "لا يمكن تعديل القالب لأن حالته \"{$currentStatus->label()}\". يجب أن يكون في حالة \"مسودة\" للتعديل.";
        }

        parent::__construct($message);
    }

    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    public function getCurrentStatus(): TemplateStatus
    {
        return $this->currentStatus;
    }
}
