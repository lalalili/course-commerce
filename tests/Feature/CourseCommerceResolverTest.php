<?php

use Lalalili\CommerceCore\Models\Product;
use Lalalili\CommerceCore\Models\ProductUser;
use Lalalili\CourseCommerce\Exceptions\CourseAlreadyPurchasedException;
use Lalalili\CourseCommerce\Exceptions\CourseProductMissingException;
use Lalalili\CourseCommerce\Support\CommerceCourseAccessResolver;
use Lalalili\CourseCommerce\Support\CommerceCourseProductResolver;
use Lalalili\CourseCommerce\Support\CourseCommerceCheckoutService;
use Lalalili\CourseCommerce\Support\CourseCommerceProductBindingService;
use Lalalili\CourseCommerce\Tests\Models\TestCourse;
use Lalalili\CourseCommerce\Tests\Models\TestUser;
use Lalalili\CourseCore\Contracts\CourseAccessResolver;
use Lalalili\CourseCore\Contracts\CourseProductResolver;

it('binds the course access and product resolvers', function (): void {
    expect(app(CourseProductResolver::class))->toBeInstanceOf(CommerceCourseProductResolver::class)
        ->and(app(CourseAccessResolver::class))->toBeInstanceOf(CommerceCourseAccessResolver::class);
});

it('resolves a course product from configured foreign keys', function (): void {
    $product = Product::query()->create([
        'title'       => 'Course product',
        'sales_price' => 1200,
    ]);
    $course = new TestCourse([
        'product_id' => $product->getKey(),
    ]);

    expect(app(CourseProductResolver::class)->productForCourse($course)?->getKey())->toBe($product->getKey());
});

it('allows purchased users to view courses and paid units', function (): void {
    $product = Product::query()->create([
        'title'       => 'Paid course',
        'sales_price' => 1200,
    ]);
    ProductUser::query()->create([
        'product_id' => $product->getKey(),
        'user_id'    => 9,
        'created_at' => now(),
    ]);

    $course = new TestCourse([
        'product_id' => $product->getKey(),
    ]);
    $unit = new TestCourse([
        'is_free' => false,
    ]);

    $resolver = app(CourseAccessResolver::class);

    expect($resolver->hasPurchasedCourse(new TestUser(9), $course))->toBeTrue()
        ->and($resolver->canViewCourse(new TestUser(9), $course))->toBeTrue()
        ->and($resolver->canAccessUnit(new TestUser(9), $course, $unit))->toBeTrue()
        ->and($resolver->canViewCourse(new TestUser(8), $course))->toBeFalse();
});

it('allows free courses and free preview units without purchase', function (): void {
    $paidProduct = Product::query()->create([
        'title'       => 'Course product',
        'sales_price' => 1200,
    ]);
    $freeProduct = Product::query()->create([
        'title'       => 'Free course product',
        'sales_price' => 0,
    ]);

    $resolver = app(CourseAccessResolver::class);
    $freeCourse = new TestCourse([
        'product_id' => $freeProduct->getKey(),
    ]);
    $paidCourse = new TestCourse([
        'product_id' => $paidProduct->getKey(),
    ]);
    $freeUnit = new TestCourse([
        'free_preview' => true,
    ]);

    expect($resolver->canViewCourse(null, $freeCourse))->toBeTrue()
        ->and($resolver->canAccessUnit(null, $paidCourse, $freeUnit))->toBeTrue();
});

it('creates a commerce order for a course product', function (): void {
    $product = Product::query()->create([
        'title'       => 'Paid course',
        'sales_price' => 1200,
    ]);
    $course = new TestCourse([
        'id'         => 22,
        'product_id' => $product->getKey(),
    ]);
    $course->exists = true;

    $order = app(CourseCommerceCheckoutService::class)->createOrderForCourse(9, $course, [
        'number' => '260510COURSE1',
    ]);

    expect($order->number)->toBe('260510COURSE1')
        ->and($order->user_id)->toBe(9)
        ->and($order->total_sales_price)->toBe(1200)
        ->and($order->details)->toHaveCount(1)
        ->and($order->details->first()?->product_id)->toBe($product->getKey());
});

it('creates and binds a commerce product from course attributes', function (): void {
    $course = new TestCourse([
        'id'         => 31,
        'title'      => 'Laravel 線上課程',
        'subtitle'   => '第一階段課程商品',
        'list_price' => 1500,
        'price'      => 1200,
    ]);

    $product = app(CourseCommerceProductBindingService::class)->syncProductForCourse($course);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->title)->toBe('Laravel 線上課程')
        ->and($product->subtitle)->toBe('第一階段課程商品')
        ->and($product->list_price)->toBe(1500)
        ->and($product->sales_price)->toBe(1200)
        ->and($course->product_id)->toBe($product->getKey())
        ->and(app(CourseProductResolver::class)->productForCourse($course)?->getKey())->toBe($product->getKey());
});

it('updates an existing bound commerce product from course attributes', function (): void {
    $product = Product::query()->create([
        'title'       => '舊課程商品',
        'list_price'  => 1000,
        'sales_price' => 800,
    ]);
    $course = new TestCourse([
        'id'         => 32,
        'product_id' => $product->getKey(),
        'title'      => '新課程商品',
        'price'      => 900,
    ]);

    $syncedProduct = app(CourseCommerceProductBindingService::class)->syncProductForCourse($course, [
        'number' => 'COURSE-32',
    ]);

    expect($syncedProduct->getKey())->toBe($product->getKey())
        ->and($syncedProduct->title)->toBe('新課程商品')
        ->and($syncedProduct->number)->toBe('COURSE-32')
        ->and($syncedProduct->sales_price)->toBe(900)
        ->and(Product::query()->count())->toBe(1);
});

it('rejects checkout when a course has no commerce product binding', function (): void {
    $course = new TestCourse(['id' => 23]);
    $course->exists = true;

    app(CourseCommerceCheckoutService::class)->createOrderForCourse(9, $course);
})->throws(CourseProductMissingException::class);

it('rejects duplicate course purchases by default', function (): void {
    $product = Product::query()->create([
        'title'       => 'Paid course',
        'sales_price' => 1200,
    ]);
    ProductUser::query()->create([
        'product_id' => $product->getKey(),
        'user_id'    => 9,
        'created_at' => now(),
    ]);
    $course = new TestCourse([
        'id'         => 24,
        'product_id' => $product->getKey(),
    ]);
    $course->exists = true;

    app(CourseCommerceCheckoutService::class)->createOrderForCourse(9, $course);
})->throws(CourseAlreadyPurchasedException::class);
