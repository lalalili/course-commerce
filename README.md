# Course Commerce

Commerce adapter between `lalalili/course-core` and `lalalili/commerce-core`.

## Install

```bash
composer require lalalili/course-commerce
php artisan vendor:publish --tag=course-commerce-config
```

For GitHub installs before a Packagist release:

```json
{
    "repositories": [
        {"type": "vcs", "url": "https://github.com/lalalili/course-core.git"},
        {"type": "vcs", "url": "https://github.com/lalalili/commerce-core.git"},
        {"type": "vcs", "url": "https://github.com/lalalili/course-commerce.git"}
    ]
}
```

## Usage

Bind or update a commerce product from a course model:

```php
use Lalalili\CourseCommerce\Support\CourseCommerceProductBindingService;

$product = app(CourseCommerceProductBindingService::class)
    ->syncProductForCourse($course);
```

Create a product if needed, create an order, and optionally get a payment URL:

```php
use Lalalili\CourseCommerce\Support\CourseCommerceCheckoutService;

$checkout = app(CourseCommerceCheckoutService::class)->checkoutCourse(
    userId: $user->id,
    course: $course,
    orderAttributes: ['invoice_type' => 3],
);

return redirect($checkout->checkoutUrl);
```

Configure the payment route in `config/course-commerce.php`:

```php
'checkout' => [
    'payment_route' => 'ecpay.checkout',
    'payment_route_parameter' => 'order',
    'result_route' => 'courses.checkout.result',
],
```

Purchase status helpers are available through:

```php
app(Lalalili\CourseCommerce\Support\CourseCommercePurchaseStatusService::class)
    ->status($user, $course);
```
