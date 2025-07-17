<?php

namespace App\Providers;

use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Integrations\WooCommerce\WooCommerceClient;
use Illuminate\Support\ServiceProvider;

/**
 * E-commerce Service Provider
 *
 * Registra dependências do sistema de e-commerce
 * Demonstra: Dependency Injection, Service Container
 */
class EcommerceServiceProvider extends ServiceProvider
{
    /**
     * Registrar serviços
     */
    public function register(): void
    {
        // Registrar repositórios
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        // Registrar clientes de integração
        $this->app->singleton(WooCommerceClient::class, function ($app) {
            return new WooCommerceClient();
        });

        // Registrar configurações customizadas
        $this->mergeConfigFrom(
            __DIR__.'/../../config/woocommerce.php', 'woocommerce'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/payment.php', 'payment'
        );
    }

    /**
     * Bootstrap de serviços
     */
    public function boot(): void
    {
        // Publicar configurações
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/woocommerce.php' => config_path('woocommerce.php'),
            ], 'woocommerce-config');

            $this->publishes([
                __DIR__.'/../../config/payment.php' => config_path('payment.php'),
            ], 'payment-config');
        }
    }

    /**
     * Serviços fornecidos por este provider
     */
    public function provides(): array
    {
        return [
            ProductRepositoryInterface::class,
            WooCommerceClient::class,
        ];
    }
}
