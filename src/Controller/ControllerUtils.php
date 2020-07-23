<?php

namespace App\Controller;

class ControllerUtils
{
    static function coursePathExists($courseInstance, $courseId): bool
    {
        if ($courseInstance) {
            $courseCode = $courseInstance->getCourse()->getCode();
            return $courseCode === $courseId;
        }

        return false;
    }
}
