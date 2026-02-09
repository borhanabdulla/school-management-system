<?php

namespace App\Domains\Academic\ClassSection\Exceptions;

use Exception;

/**
 * ClassroomFullException - استثناء امتلاء الفصل
 */
class ClassroomFullException extends Exception
{
    protected $classSection;
    protected $currentCapacity;
    protected $maxCapacity;

    public function __construct($classSection, $message = null)
    {
        $this->classSection = $classSection;
        $this->currentCapacity = $classSection->current_capacity ?? 0;
        $this->maxCapacity = $classSection->max_capacity;

        $message = $message ?? "الفصل {$classSection->name} ممتلئ ({$this->currentCapacity}/{$this->maxCapacity}). لا يمكن إضافة المزيد من الطلاب.";

        parent::__construct($message);
    }

    public function getClassSection()
    {
        return $this->classSection;
    }

    public function getCurrentCapacity()
    {
        return $this->currentCapacity;
    }

    public function getMaxCapacity()
    {
        return $this->maxCapacity;
    }
}
