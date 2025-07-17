<?php

namespace App\Jobs;

use App\Models\Product;
use App\Integrations\WooCommerce\WooCommerceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Sync Product to WooCommerce Job
 *
 * Job assíncrono para sincronizar produto com WooCommerce
 * Demonstra: Queue Jobs, Background Processing
 */
class SyncProductToWooCommerceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas
     */
    public $tries = 3;

    /**
     * Timeout em segundos
     */
    public $timeout = 60;

    public function __construct(
        private Product $product
    ) {}

    /**
     * Executar o job
     */
    public function handle(WooCommerceClient $wooCommerceClient): void
    {
        try {
            Log::info('Iniciando sincronização de produto com WooCommerce', [
                'product_id' => $this->product->id,
                'product_name' => $this->product->name
            ]);

            $productData = $this->mapProductToWooCommerce($this->product);

            if ($this->product->woocommerce_id) {
                // Atualizar produto existente
                $wooProduct = $wooCommerceClient->updateProduct(
                    $this->product->woocommerce_id,
                    $productData
                );
            } else {
                // Criar novo produto
                $wooProduct = $wooCommerceClient->createProduct($productData);

                // Salvar ID do WooCommerce no produto local
                $this->product->update([
                    'woocommerce_id' => $wooProduct['id']
                ]);
            }

            Log::info('Produto sincronizado com sucesso no WooCommerce', [
                'product_id' => $this->product->id,
                'woocommerce_id' => $wooProduct['id']
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar produto com WooCommerce', [
                'product_id' => $this->product->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Re-lançar exceção para retry automático
            throw $e;
        }
    }

    /**
     * Ação executada quando job falha após todas as tentativas
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de sincronização com WooCommerce falhou permanentemente', [
            'product_id' => $this->product->id,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Mapear produto Laravel para formato WooCommerce
     */
    private function mapProductToWooCommerce(Product $product): array
    {
        $data = [
            'name' => $product->name,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'sku' => $product->sku,
            'regular_price' => (string) $product->price,
            'status' => $product->status,
            'featured' => $product->featured,
            'catalog_visibility' => $product->catalog_visibility,
            'manage_stock' => $product->manage_stock,
            'in_stock' => $product->in_stock,
        ];

        // Preço promocional
        if ($product->sale_price && $product->sale_price > 0) {
            $data['sale_price'] = (string) $product->sale_price;
        }

        // Estoque
        if ($product->manage_stock) {
            $data['stock_quantity'] = $product->stock_quantity;
        }

        // Peso
        if ($product->weight) {
            $data['weight'] = (string) $product->weight;
        }

        // Dimensões
        if ($product->dimensions && is_array($product->dimensions)) {
            $data['dimensions'] = [
                'length' => (string) ($product->dimensions['length'] ?? ''),
                'width' => (string) ($product->dimensions['width'] ?? ''),
                'height' => (string) ($product->dimensions['height'] ?? ''),
            ];
        }

        // Imagens
        if ($product->images && is_array($product->images)) {
            $data['images'] = $product->images;
        }

        // Categorias
        if ($product->categories->isNotEmpty()) {
            $data['categories'] = $product->categories->map(function ($category) {
                return ['id' => $category->woocommerce_id];
            })->filter(function ($category) {
                return !is_null($category['id']);
            })->values()->toArray();
        }

        // Meta data customizada
        if ($product->meta_data && is_array($product->meta_data)) {
            $data['meta_data'] = $product->meta_data;
        }

        return $data;
    }
}
