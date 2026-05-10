<?php

use Lalalili\CommerceCore\Models\Product;
use Lalalili\CommerceCore\Models\ProductUser;
use Lalalili\CourseCommerce\Support\CommerceCourseAccessResolver;
use Lalalili\CourseCommerce\Support\CommerceCourseProductResolver;
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
