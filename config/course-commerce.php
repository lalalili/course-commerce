<?php

return [
    'bind_course_resolvers' => true,

    'course_product' => [
        'relation'     => 'product',
        'foreign_keys' => [
            'product_id',
            'commerce_product_id',
        ],
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
