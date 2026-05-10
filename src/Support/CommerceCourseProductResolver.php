<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lalalili\CommerceCore\Models\Product;
use Lalalili\CourseCore\Contracts\CourseProductResolver;

class CommerceCourseProductResolver implements CourseProductResolver
{
    public function productForCourse(Model $course): ?Model
    {
        $relationName = (string) config('course-commerce.course_product.relation', 'product');
        $relatedProduct = $this->productFromRelation($course, $relationName);

        if ($relatedProduct instanceof Model) {
            return $relatedProduct;
        }

        foreach ($this->foreignKeys() as $foreignKey) {
            $productId = data_get($course, $foreignKey);

            if (filled($productId)) {
                return $this->productModel()::query()->find($productId);
            }
        }

        return null;
    }

    private function productFromRelation(Model $course, string $relationName): ?Model
    {
        if ($course->relationLoaded($relationName)) {
            $product = $course->getRelation($relationName);

            return $product instanceof Model ? $product : null;
        }

        if (! method_exists($course, $relationName)) {
            return null;
        }

        $relation = $course->{$relationName}();

        if (! $relation instanceof Relation) {
            return null;
        }

        $product = $relation->getResults();

        return $product instanceof Model ? $product : null;
    }

    /**
     * @return list<string>
     */
    private function foreignKeys(): array
    {
        $foreignKeys = config('course-commerce.course_product.foreign_keys', []);

        if (! is_array($foreignKeys)) {
            return [];
        }

        return array_values(array_filter($foreignKeys, is_string(...)));
    }

    /**
     * @return class-string<Model>
     */
    private function productModel(): string
    {
        /** @var class-string<Model> $productModel */
        $productModel = config('commerce.models.product', Product::class);

        return $productModel;
    }
}
