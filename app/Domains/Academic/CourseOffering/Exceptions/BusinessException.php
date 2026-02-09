<?php

namespace App\Domains\Academic\CourseOffering\Exceptions;

use Exception;

/**
 * BusinessException - استثناء عمليات CourseOffering
 */
class BusinessException extends Exception
{
    public function render($request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return redirect()->back()->with('error', $this->getMessage());
    }
}
