<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CommerceCore\Models\ProductUser;
use Lalalili\CourseCore\Contracts\CourseAccessResolver;
use Lalalili\CourseCore\Contracts\CourseProductResolver;

class CommerceCourseAccessResolver implements CourseAccessResolver
{
    public function __construct(private readonly CourseProductResolver $productResolver)
    {
    }

    public function canViewCourse(?Authenticatable $user, Model $course): bool
    {
        return $this->isFreeCourse($course)
            || $this->hasPurchasedCourse($user, $course);
    }

    public function canAccessUnit(?Authenticatable $user, Model $course, Model $unit): bool
    {
        return $this->isFreeUnit($unit)
            || $this->canViewCourse($user, $course);
    }

    public function hasPurchasedCourse(?Authenticatable $user, Model $course): bool
    {
        if (! $user instanceof Authenticatable) {
            return false;
        }

        $product = $this->productResolver->productForCourse($course);

        if (! $product instanceof Model) {
            return false;
        }

        return $this->productUserModel()::query()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getAuthIdentifier())
            ->exists();
    }

    private function isFreeCourse(Model $course): bool
    {
        foreach ($this->booleanFlags('free_course_flags') as $flag) {
            if ((bool) data_get($course, $flag, false)) {
                return true;
            }
        }

        $product = $this->productResolver->productForCourse($course);

        return $product instanceof Model
            && (int) data_get($product, 'sales_price', data_get($product, 'price', 0)) === 0;
    }

    private function isFreeUnit(Model $unit): bool
    {
        foreach ($this->booleanFlags('free_unit_flags') as $flag) {
            if ((bool) data_get($unit, $flag, false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function booleanFlags(string $key): array
    {
        $flags = config("course-commerce.access.{$key}", []);

        if (! is_array($flags)) {
            return [];
        }

        return array_values(array_filter($flags, is_string(...)));
    }

    /**
     * @return class-string<Model>
     */
    private function productUserModel(): string
    {
        /** @var class-string<Model> $productUserModel */
        $productUserModel = config('commerce.models.product_user', ProductUser::class);

        return $productUserModel;
    }
}
