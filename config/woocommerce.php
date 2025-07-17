<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WooCommerce Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com WooCommerce
    |
    */

    'base_url' => env('WOOCOMMERCE_BASE_URL', 'https://example.com'),

    'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),

    'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'),

    'ssl_verify' => env('WOOCOMMERCE_SSL_VERIFY', true),

    'auto_sync' => env('WOOCOMMERCE_AUTO_SYNC', true),

    'sync_batch_size' => env('WOOCOMMERCE_SYNC_BATCH_SIZE', 50),

    'webhook_secret' => env('WOOCOMMERCE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Mapping Configuration
    |--------------------------------------------------------------------------
    |
    | Mapeamento de campos entre Laravel e WooCommerce
    |
    */

    'field_mapping' => [
        'product' => [
            'name' => 'name',
            'description' => 'description',
            'short_description' => 'short_description',
            'sku' => 'sku',
            'regular_price' => 'price',
            'sale_price' => 'sale_price',
            'status' => 'status',
            'featured' => 'featured',
            'manage_stock' => 'manage_stock',
            'stock_quantity' => 'stock_quantity',
            'in_stock' => 'in_stock',
            'weight' => 'weight',
            'dimensions' => 'dimensions',
            'images' => 'images',
            'categories' => 'category_ids',
            'meta_data' => 'meta_data',
        ],

        'order' => [
            'status' => 'status',
            'currency' => 'currency',
            'total' => 'total',
            'subtotal' => 'subtotal',
            'tax_total' => 'tax_total',
            'shipping_total' => 'shipping_total',
            'discount_total' => 'discount_total',
            'payment_method' => 'payment_method',
            'payment_method_title' => 'payment_method_title',
            'transaction_id' => 'transaction_id',
            'billing' => 'billing_address',
            'shipping' => 'shipping_address',
            'customer_note' => 'customer_note',
            'meta_data' => 'meta_data',
            'date_paid' => 'date_paid',
            'date_completed' => 'date_completed',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Mapping
    |--------------------------------------------------------------------------
    |
    | Mapeamento de status entre sistemas
    |
    */

    'status_mapping' => [
        'product' => [
            'draft' => 'draft',
            'pending' => 'pending',
            'private' => 'private',
            'publish' => 'publish',
        ],

        'order' => [
            'pending' => 'pending',
            'processing' => 'processing',
            'on-hold' => 'on-hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'failed' => 'failed',
        ]
    ],
];
