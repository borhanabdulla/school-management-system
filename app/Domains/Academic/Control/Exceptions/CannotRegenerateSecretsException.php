<?php

namespace App\Domains\Academic\Control\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عند محاولة إعادة توليد الأرقام السرية بعد بدء الامتحانات
 */
class CannotRegenerateSecretsException extends InvalidOperationException
{
    public function __construct()
    {
        parent::__construct(
            'لا يمكن إعادة توليد الأرقام السرية بعد بدء الامتحانات.',
            'regenerate_secrets',
            'exam_already_started'
        );
    }
}
