<?php

$loader = require __DIR__.'/../../../vendor/autoload.php';

$loader->addPsr4('Lalalili\\CommerceCore\\', __DIR__.'/../../commerce-core/src/', true);
$loader->addPsr4('Lalalili\\CourseCore\\', __DIR__.'/../../course-core/src/', true);
$loader->addPsr4('Lalalili\\CourseCommerce\\', __DIR__.'/../src/', true);
$loader->addPsr4('Lalalili\\CourseCommerce\\Tests\\', __DIR__.'/', true);
$loader->addClassMap([
    'Lalalili\\CourseCommerce\\CourseCommerceServiceProvider' => __DIR__.'/../src/CourseCommerceServiceProvider.php',
    'Lalalili\\CourseCommerce\\Support\\CourseCommerceCheckoutService' => __DIR__.'/../src/Support/CourseCommerceCheckoutService.php',
    'Lalalili\\CourseCommerce\\Support\\CourseCommerceProductBindingService' => __DIR__.'/../src/Support/CourseCommerceProductBindingService.php',
    'Lalalili\\CourseCommerce\\Support\\CourseCommercePurchaseStatusService' => __DIR__.'/../src/Support/CourseCommercePurchaseStatusService.php',
    'Lalalili\\CourseCommerce\\Support\\CourseCommerceRedirectService' => __DIR__.'/../src/Support/CourseCommerceRedirectService.php',
]);

return $loader;
