<?php

namespace Lalalili\CourseCommerce\Exceptions;

use RuntimeException;

class CourseProductMissingException extends RuntimeException
{
    public static function forCourse(mixed $courseKey): self
    {
        return new self("Course [{$courseKey}] does not have a commerce product binding.");
    }
}
