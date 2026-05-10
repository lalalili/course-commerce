<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lalalili\CommerceCore\Models\Product;
use Lalalili\CourseCore\Contracts\CourseProductResolver;

class CourseCommerceProductBindingService
{
    /**
     * @var array<string, list<string>>
     */
    private const DEFAULT_FIELD_MAPS = [
        'foreign_keys' => [
            'product_id',
            'commerce_product_id',
        ],
        'title_fields' => [
            'title',
            'name',
        ],
        'subtitle_fields' => [
            'subtitle',
            'sub_title',
        ],
        'list_price_fields' => [
            'list_price',
            'price',
            'origin_price',
        ],
        'sales_price_fields' => [
            'sales_price',
            'sale_price',
            'price',
        ],
        'product_attribute_fields' => [],
    ];

    /**
     * @var array<string, mixed>
     */
    private const DEFAULT_PRODUCT_VALUES = [
        'type' => 1,
        'tax' => 1,
        'active' => true,
    ];

    public function __construct(private readonly CourseProductResolver $products) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function syncProductForCourse(Model $course, array $attributes = []): Model
    {
        /** @var class-string<Model> $productModel */
        $productModel = config('commerce.models.product', Product::class);

        return DB::transaction(function () use ($course, $attributes, $productModel): Model {
            $product = $this->products->productForCourse($course);
            $productAttributes = array_merge($this->productAttributesFromCourse($course), $attributes);
            $productAttributes = $this->filterModelAttributes($product ?? new $productModel, $productAttributes);

            if ($product instanceof Model) {
                $product->forceFill($productAttributes)->save();
                $product = $product->refresh();
            } else {
                $product = $productModel::query()->create($productAttributes);
            }

            $this->bindProductToCourse($course, $product);

            return $product;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function productAttributesFromCourse(Model $course): array
    {
        $listPrice = $this->firstInteger($course, $this->configuredFields('list_price_fields'));
        $salesPrice = $this->firstInteger($course, $this->configuredFields('sales_price_fields'));

        return array_merge($this->defaults(), $this->mappedProductAttributes($course), [
            'title' => $this->firstString($course, $this->configuredFields('title_fields')) ?: 'Untitled course',
            'subtitle' => $this->firstString($course, $this->configuredFields('subtitle_fields')),
            'list_price' => $listPrice ?? $salesPrice ?? 0,
            'sales_price' => $salesPrice ?? $listPrice ?? 0,
        ]);
    }

    private function bindProductToCourse(Model $course, Model $product): void
    {
        $foreignKey = $this->bindingForeignKey();

        if ($foreignKey !== null) {
            $course->forceFill([$foreignKey => $product->getKey()]);

            if ($course->exists && $this->modelHasColumn($course, $foreignKey)) {
                $course->save();
            }
        }

        $relationName = (string) config('course-commerce.course_product.relation', 'product');

        if ($relationName !== '') {
            $course->setRelation($relationName, $product);
        }
    }

    private function modelHasColumn(Model $model, string $column): bool
    {
        return $model->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($model->getTable(), $column);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterModelAttributes(Model $model, array $attributes): array
    {
        return collect($attributes)
            ->filter(fn (mixed $value, string $column): bool => $this->modelHasColumn($model, $column))
            ->all();
    }

    private function bindingForeignKey(): ?string
    {
        return $this->configuredFields('foreign_keys')[0] ?? null;
    }

    /**
     * @return list<string>
     */
    private function configuredFields(string $key): array
    {
        $fields = config("course-commerce.course_product.{$key}", []);

        if (! is_array($fields)) {
            return self::DEFAULT_FIELD_MAPS[$key] ?? [];
        }

        $fields = array_values(array_filter($fields, is_string(...)));

        return $fields === [] ? self::DEFAULT_FIELD_MAPS[$key] ?? [] : $fields;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        $defaults = config('course-commerce.course_product.defaults', []);

        if (! is_array($defaults)) {
            return self::DEFAULT_PRODUCT_VALUES;
        }

        return $defaults === [] ? self::DEFAULT_PRODUCT_VALUES : $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    private function mappedProductAttributes(Model $course): array
    {
        $fieldMap = config('course-commerce.course_product.product_attribute_fields', []);

        if (! is_array($fieldMap)) {
            return [];
        }

        $attributes = [];

        foreach ($fieldMap as $productField => $courseFields) {
            if (! is_string($productField) || $productField === '') {
                continue;
            }

            $fields = is_array($courseFields) ? $courseFields : [$courseFields];

            foreach ($fields as $courseField) {
                if (! is_string($courseField) || $courseField === '') {
                    continue;
                }

                $value = data_get($course, $courseField);

                if ($value !== null && $value !== '') {
                    $attributes[$productField] = $value;

                    break;
                }
            }
        }

        return $attributes;
    }

    /**
     * @param  list<string>  $fields
     */
    private function firstString(Model $course, array $fields): ?string
    {
        foreach ($fields as $field) {
            $value = data_get($course, $field);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $fields
     */
    private function firstInteger(Model $course, array $fields): ?int
    {
        foreach ($fields as $field) {
            $value = data_get($course, $field);

            if (is_numeric($value)) {
                return max(0, (int) $value);
            }
        }

        return null;
    }
}
