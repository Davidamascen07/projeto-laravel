<?php

namespace App\Integrations\WooCommerce;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * WooCommerce API Client
 *
 * Cliente para integração com API REST do WooCommerce
 * Demonstra: API Integration, HTTP Client, Error Handling
 */
class WooCommerceClient
{
    private Client $httpClient;
    private ?string $baseUrl;
    private ?string $consumerKey;
    private ?string $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = config('woocommerce.base_url') ?: 'https://example.com';
        $this->consumerKey = config('woocommerce.consumer_key') ?: '';
        $this->consumerSecret = config('woocommerce.consumer_secret') ?: '';

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => config('woocommerce.ssl_verify', true),
        ]);
    }

    /**
     * Buscar produtos do WooCommerce
     */
    public function getProducts(int $page = 1, int $perPage = 100): array
    {
        try {
            $response = $this->httpClient->get('wp-json/wc/v3/products', [
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar produtos do WooCommerce', [
                'error' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage
            ]);

            throw new \Exception('Falha na comunicação com WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Buscar produto específico do WooCommerce
     */
    public function getProduct(int $productId): array
    {
        try {
            $response = $this->httpClient->get("wp-json/wc/v3/products/{$productId}", [
                'query' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar produto do WooCommerce', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);

            throw new \Exception('Produto não encontrado no WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Criar produto no WooCommerce
     */
    public function createProduct(array $productData): array
    {
        try {
            $response = $this->httpClient->post('wp-json/wc/v3/products', [
                'query' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ],
                'json' => $productData
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Produto criado no WooCommerce', [
                'product_id' => $result['id'],
                'name' => $result['name']
            ]);

            return $result;
        } catch (RequestException $e) {
            Log::error('Erro ao criar produto no WooCommerce', [
                'error' => $e->getMessage(),
                'product_data' => $productData
            ]);

            throw new \Exception('Falha ao criar produto no WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar produto no WooCommerce
     */
    public function updateProduct(int $productId, array $productData): array
    {
        try {
            $response = $this->httpClient->put("wp-json/wc/v3/products/{$productId}", [
                'query' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ],
                'json' => $productData
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Produto atualizado no WooCommerce', [
                'product_id' => $productId,
                'name' => $result['name']
            ]);

            return $result;
        } catch (RequestException $e) {
            Log::error('Erro ao atualizar produto no WooCommerce', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'product_data' => $productData
            ]);

            throw new \Exception('Falha ao atualizar produto no WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Deletar produto no WooCommerce
     */
    public function deleteProduct(int $productId, bool $force = false): array
    {
        try {
            $response = $this->httpClient->delete("wp-json/wc/v3/products/{$productId}", [
                'query' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                    'force' => $force
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Produto deletado no WooCommerce', [
                'product_id' => $productId,
                'force' => $force
            ]);

            return $result;
        } catch (RequestException $e) {
            Log::error('Erro ao deletar produto no WooCommerce', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);

            throw new \Exception('Falha ao deletar produto no WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Buscar pedidos do WooCommerce
     */
    public function getOrders(int $page = 1, int $perPage = 100, array $filters = []): array
    {
        try {
            $query = array_merge([
                'page' => $page,
                'per_page' => $perPage,
                'consumer_key' => $this->consumerKey,
                'consumer_secret' => $this->consumerSecret,
            ], $filters);

            $response = $this->httpClient->get('wp-json/wc/v3/orders', [
                'query' => $query
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar pedidos do WooCommerce', [
                'error' => $e->getMessage(),
                'page' => $page,
                'filters' => $filters
            ]);

            throw new \Exception('Falha na comunicação com WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar status do pedido no WooCommerce
     */
    public function updateOrderStatus(int $orderId, string $status): array
    {
        try {
            $response = $this->httpClient->put("wp-json/wc/v3/orders/{$orderId}", [
                'query' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ],
                'json' => ['status' => $status]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Status do pedido atualizado no WooCommerce', [
                'order_id' => $orderId,
                'status' => $status
            ]);

            return $result;
        } catch (RequestException $e) {
            Log::error('Erro ao atualizar status do pedido no WooCommerce', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'status' => $status
            ]);

            throw new \Exception('Falha ao atualizar pedido no WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Testar conexão com WooCommerce
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->httpClient->get('wp-json/wc/v3', [
                'query' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            Log::error('Falha no teste de conexão com WooCommerce', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
