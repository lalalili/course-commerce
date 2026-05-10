<?php

return [
    'bind_course_resolvers' => true,

    'course_product' => [
        'relation'     => 'product',
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
        'defaults' => [
            'type'   => 1,
            'tax'    => 1,
            'active' => true,
        ],
    ],

    'checkout' => [
        'prevent_duplicate_purchase' => true,
    ],

    'access' => [
        'free_course_flags' => [
            'isFree',
            'is_free',
            'free',
        ],
        'free_unit_flags' => [
            'isFree',
            'is_free',
            'free_preview',
        ],
    ],
];
