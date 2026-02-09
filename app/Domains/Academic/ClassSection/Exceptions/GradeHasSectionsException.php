<?php

namespace App\Domains\Academic\ClassSection\Exceptions;

use Exception;

class GradeHasSectionsException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'لا يمكن حذف الصف لأنه يحتوي على شعب دراسية.'
        ], 422);
    }
}
