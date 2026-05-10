<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Database\Eloquent\Model;
use Lalalili\CommerceCore\Models\Order;

class CourseCommerceRedirectService
{
    public function paymentResultUrl(Order $order, ?Model $course = null): ?string
    {
        $routeName = config('course-commerce.checkout.result_route');

        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        $parameters = [
            $this->orderKey() => $order->getKey(),
        ];

        if ($course instanceof Model) {
            $parameters[$this->courseKey()] = $course->getKey();
        }

        return route($routeName, $parameters);
    }

    private function orderKey(): string
    {
        $key = config('course-commerce.checkout.result_route_order_key', 'order');

        return is_string($key) && $key !== '' ? $key : 'order';
    }

    private function courseKey(): string
    {
        $key = config('course-commerce.checkout.result_route_course_key', 'course');

        return is_string($key) && $key !== '' ? $key : 'course';
    }
}
