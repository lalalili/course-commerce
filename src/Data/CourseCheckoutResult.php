<?php

namespace Lalalili\CourseCommerce\Data;

use Illuminate\Database\Eloquent\Model;

final readonly class CourseCheckoutResult
{
    public function __construct(
        public Model $course,
        public Model $product,
        public Model $order,
        public ?string $checkoutUrl = null,
    ) {}
}
