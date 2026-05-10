<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Database\Eloquent\Model;
use Lalalili\CommerceCore\Models\Order;
use Lalalili\CommerceCore\Services\OrderLifecycleService;
use Lalalili\CourseCommerce\Exceptions\CourseAlreadyPurchasedException;
use Lalalili\CourseCommerce\Exceptions\CourseProductMissingException;
use Lalalili\CourseCore\Contracts\CourseAccessResolver;
use Lalalili\CourseCore\Contracts\CourseProductResolver;

class CourseCommerceCheckoutService
{
    public function __construct(
        private readonly CourseProductResolver $products,
        private readonly CourseAccessResolver $access,
        private readonly OrderLifecycleService $orders,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createOrderForCourse(int $userId, Model $course, array $attributes = []): Order
    {
        $product = $this->products->productForCourse($course);

        if (! $product instanceof Model) {
            throw CourseProductMissingException::forCourse($course->getKey());
        }

        if (
            (bool) config('course-commerce.checkout.prevent_duplicate_purchase', true)
            && $this->access->hasPurchasedCourse(new CourseCommerceUserIdentity($userId), $course)
        ) {
            throw CourseAlreadyPurchasedException::forCourse($course->getKey());
        }

        return $this->orders->create($userId, [
            [
                'product_id' => $product->getKey(),
                'qty'        => 1,
            ],
        ], $attributes);
    }
}
