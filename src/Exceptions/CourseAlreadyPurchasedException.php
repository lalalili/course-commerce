<?php

namespace Lalalili\CourseCommerce\Exceptions;

use RuntimeException;

class CourseAlreadyPurchasedException extends RuntimeException
{
    public static function forCourse(mixed $courseKey): self
    {
        return new self("Course [{$courseKey}] has already been purchased by this user.");
    }
}
