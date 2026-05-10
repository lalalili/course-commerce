<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CourseCore\Contracts\CourseAccessResolver;
use Lalalili\CourseCore\Contracts\CourseProductResolver;

class CourseCommercePurchaseStatusService
{
    public function __construct(
        private readonly CourseProductResolver $products,
        private readonly CourseAccessResolver $access,
    ) {
    }

    /**
     * @return array{has_product: bool, purchased: bool, can_view: bool}
     */
    public function status(?Authenticatable $user, Model $course): array
    {
        return [
            'has_product' => $this->products->productForCourse($course) instanceof Model,
            'purchased'   => $this->hasPurchased($user, $course),
            'can_view'    => $this->canView($user, $course),
        ];
    }

    public function hasPurchased(?Authenticatable $user, Model $course): bool
    {
        return $this->access->hasPurchasedCourse($user, $course);
    }

    public function canView(?Authenticatable $user, Model $course): bool
    {
        return $this->access->canViewCourse($user, $course);
    }
}
