<?php

namespace Lalalili\CourseCommerce\Data;

use Illuminate\Database\Eloquent\Model;
use Lalalili\CommerceCore\Models\Order;

final readonly class CourseCheckoutResult
{
    public function __construct(
        public Model $course,
        public Model $product,
        public Order $order,
        public ?string $checkoutUrl = null,
    ) {
    }
}
