<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Database\Eloquent\Model;
use Lalalili\CommerceCore\Services\OrderLifecycleService;
use Lalalili\CourseCommerce\Data\CourseCheckoutResult;
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
        private readonly CourseCommerceProductBindingService $productBindings,
    ) {}

    /**
     * @param  array<string, mixed>  $orderAttributes
     * @param  array<string, mixed>  $productAttributes
     */
    public function checkoutCourse(
        int $userId,
        Model $course,
        array $orderAttributes = [],
        array $productAttributes = [],
    ): CourseCheckoutResult {
        $product = $this->productBindings->syncProductForCourse($course, $productAttributes);
        $order = $this->createOrderForCourse($userId, $course, $orderAttributes);

        return new CourseCheckoutResult(
            course: $course,
            product: $product,
            order: $order,
            checkoutUrl: $this->checkoutUrl($order),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createOrderForCourse(int $userId, Model $course, array $attributes = []): Model
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
                'qty' => 1,
            ],
        ], $attributes);
    }

    private function checkoutUrl(Model $order): ?string
    {
        $routeName = config('course-commerce.checkout.payment_route');

        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        $parameter = config('course-commerce.checkout.payment_route_parameter', 'order');

        return route($routeName, [
            is_string($parameter) && $parameter !== '' ? $parameter : 'order' => $order->getKey(),
        ]);
    }
}
