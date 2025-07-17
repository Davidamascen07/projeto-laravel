<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para gateways de pagamento
    |
    */

    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    'currency' => env('PAYMENT_CURRENCY', 'BRL'),

    'test_mode' => env('PAYMENT_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Gateway Providers
    |--------------------------------------------------------------------------
    |
    | Provedores de pagamento disponíveis
    |
    */

    'gateways' => [
        'stripe' => [
            'name' => 'Stripe',
            'class' => \App\Services\Payment\StripePaymentGateway::class,
            'config' => [
                'public_key' => env('STRIPE_PUBLIC_KEY'),
                'secret_key' => env('STRIPE_SECRET_KEY'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
                'api_version' => '2023-10-16',
            ],
        ],

        'pagseguro' => [
            'name' => 'PagSeguro',
            'class' => \App\Services\Payment\PagSeguroPaymentGateway::class,
            'config' => [
                'client_id' => env('PAGSEGURO_CLIENT_ID'),
                'client_secret' => env('PAGSEGURO_CLIENT_SECRET'),
                'sandbox' => env('PAGSEGURO_SANDBOX', true),
            ],
        ],

        'mercadopago' => [
            'name' => 'Mercado Pago',
            'class' => \App\Services\Payment\MercadoPagoPaymentGateway::class,
            'config' => [
                'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
                'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
                'sandbox' => env('MERCADOPAGO_SANDBOX', true),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Métodos de pagamento disponíveis
    |
    */

    'methods' => [
        'credit_card' => [
            'name' => 'Cartão de Crédito',
            'enabled' => true,
            'gateways' => ['stripe', 'pagseguro', 'mercadopago'],
        ],

        'debit_card' => [
            'name' => 'Cartão de Débito',
            'enabled' => true,
            'gateways' => ['stripe', 'pagseguro'],
        ],

        'pix' => [
            'name' => 'PIX',
            'enabled' => true,
            'gateways' => ['pagseguro', 'mercadopago'],
        ],

        'boleto' => [
            'name' => 'Boleto Bancário',
            'enabled' => true,
            'gateways' => ['pagseguro', 'mercadopago'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fraud Detection
    |--------------------------------------------------------------------------
    |
    | Configurações para detecção de fraude
    |
    */

    'fraud_detection' => [
        'enabled' => env('PAYMENT_FRAUD_DETECTION', true),
        'providers' => [
            'clearsale' => [
                'enabled' => env('CLEARSALE_ENABLED', false),
                'entity_code' => env('CLEARSALE_ENTITY_CODE'),
                'api_key' => env('CLEARSALE_API_KEY'),
            ],
        ],
        'rules' => [
            'max_amount_without_verification' => 500.00,
            'max_daily_amount_per_customer' => 2000.00,
            'max_transactions_per_day' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Installments
    |--------------------------------------------------------------------------
    |
    | Configurações de parcelamento
    |
    */

    'installments' => [
        'enabled' => true,
        'max_installments' => 12,
        'min_installment_amount' => 20.00,
        'interest_rate' => 2.99, // % ao mês
        'free_installments' => 3, // parcelas sem juros
    ],
];
