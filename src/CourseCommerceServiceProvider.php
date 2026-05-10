<?php

namespace Lalalili\CourseCommerce;

use Lalalili\CourseCommerce\Support\CommerceCourseAccessResolver;
use Lalalili\CourseCommerce\Support\CommerceCourseProductResolver;
use Lalalili\CourseCommerce\Support\CourseCommerceCheckoutService;
use Lalalili\CourseCore\Contracts\CourseAccessResolver;
use Lalalili\CourseCore\Contracts\CourseProductResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CourseCommerceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('course-commerce')
            ->hasConfigFile('course-commerce');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(CommerceCourseProductResolver::class);
        $this->app->singleton(CommerceCourseAccessResolver::class);
        $this->app->singleton(CourseCommerceCheckoutService::class);
    }

    public function packageBooted(): void
    {
        if (! config('course-commerce.bind_course_resolvers', true)) {
            return;
        }

        $this->app->bind(CourseProductResolver::class, CommerceCourseProductResolver::class);
        $this->app->bind(CourseAccessResolver::class, CommerceCourseAccessResolver::class);
    }
}
