<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepositoryInterface;
use App\Integrations\WooCommerce\WooCommerceClient;
use App\Events\ProductSynced;
use App\Jobs\SyncProductToWooCommerceJob;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Product Service
 *
 * Contém a lógica de negócio para produtos
 * Demonstra: Service Layer, Dependency Injection, Events
 */
class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private WooCommerceClient $wooCommerceClient
    ) {}

    /**
     * Listar produtos com filtros
     */
    public function getProducts(array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->getWithFilters($filters);
    }

    /**
     * Buscar produto por ID
     */
    public function findProduct(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Criar novo produto
     */
    public function createProduct(array $data): Product
    {
        // Gerar SKU se não informado
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data['name']);
        }

        // Validar SKU único
        if ($this->productRepository->existsBySku($data['sku'])) {
            throw new \InvalidArgumentException('SKU já existe no sistema');
        }

        $product = $this->productRepository->create($data);

        // Disparar job para sincronizar com WooCommerce
        if (config('woocommerce.auto_sync', true)) {
            SyncProductToWooCommerceJob::dispatch($product);
        }

        // Disparar evento
        event(new ProductSynced($product, 'created'));

        return $product;
    }

    /**
     * Atualizar produto
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->findProduct($id);

        if (!$product) {
            throw new \InvalidArgumentException('Produto não encontrado');
        }

        // Validar SKU único se foi alterado
        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            if ($this->productRepository->existsBySku($data['sku'])) {
                throw new \InvalidArgumentException('SKU já existe no sistema');
            }
        }

        $updatedProduct = $this->productRepository->update($product, $data);

        // Sincronizar com WooCommerce
        if (config('woocommerce.auto_sync', true)) {
            SyncProductToWooCommerceJob::dispatch($updatedProduct);
        }

        event(new ProductSynced($updatedProduct, 'updated'));

        return $updatedProduct;
    }

    /**
     * Deletar produto
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->findProduct($id);

        if (!$product) {
            throw new \InvalidArgumentException('Produto não encontrado');
        }

        // Verificar se produto tem pedidos associados
        if ($product->orderItems()->exists()) {
            throw new \InvalidArgumentException('Não é possível deletar produto com pedidos associados');
        }

        $deleted = $this->productRepository->delete($product);

        if ($deleted) {
            event(new ProductSynced($product, 'deleted'));
        }

        return $deleted;
    }

    /**
     * Sincronizar produtos do WooCommerce
     */
    public function syncFromWooCommerce(int $page = 1, int $perPage = 100): array
    {
        $wooProducts = $this->wooCommerceClient->getProducts($page, $perPage);
        $syncResults = [
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        foreach ($wooProducts as $wooProduct) {
            try {
                $existingProduct = $this->productRepository->findByWooCommerceId($wooProduct['id']);

                $productData = $this->mapWooCommerceProductData($wooProduct);

                if ($existingProduct) {
                    $this->productRepository->update($existingProduct, $productData);
                    $syncResults['updated']++;
                } else {
                    $this->productRepository->create($productData);
                    $syncResults['created']++;
                }
            } catch (\Exception $e) {
                $syncResults['errors'][] = [
                    'product_id' => $wooProduct['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $syncResults;
    }

    /**
     * Buscar produtos em destaque
     */
    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return $this->productRepository->getFeatured($limit);
    }

    /**
     * Buscar produtos por categoria
     */
    public function getProductsByCategory(int $categoryId, array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository->getByCategory($categoryId, $filters);
    }

    /**
     * Atualizar estoque do produto
     */
    public function updateStock(int $productId, int $quantity, string $operation = 'set'): Product
    {
        $product = $this->findProduct($productId);

        if (!$product) {
            throw new \InvalidArgumentException('Produto não encontrado');
        }

        if (!$product->manage_stock) {
            throw new \InvalidArgumentException('Produto não gerencia estoque');
        }

        $newQuantity = match($operation) {
            'add' => $product->stock_quantity + $quantity,
            'subtract' => $product->stock_quantity - $quantity,
            'set' => $quantity,
            default => throw new \InvalidArgumentException('Operação inválida')
        };

        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Estoque não pode ser negativo');
        }

        $updatedProduct = $this->productRepository->update($product, [
            'stock_quantity' => $newQuantity,
            'in_stock' => $newQuantity > 0
        ]);

        return $updatedProduct;
    }

    /**
     * Gerar SKU único
     */
    private function generateSku(string $name): string
    {
        $baseSku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
        $counter = 1;

        do {
            $sku = $baseSku . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;
        } while ($this->productRepository->existsBySku($sku));

        return $sku;
    }

    /**
     * Mapear dados do WooCommerce para formato interno
     */
    private function mapWooCommerceProductData(array $wooProduct): array
    {
        return [
            'name' => $wooProduct['name'],
            'description' => $wooProduct['description'] ?? '',
            'short_description' => $wooProduct['short_description'] ?? '',
            'sku' => $wooProduct['sku'] ?? '',
            'price' => $wooProduct['regular_price'] ?? 0,
            'sale_price' => $wooProduct['sale_price'] ?? null,
            'stock_quantity' => $wooProduct['stock_quantity'] ?? 0,
            'manage_stock' => $wooProduct['manage_stock'] ?? false,
            'in_stock' => $wooProduct['in_stock'] ?? true,
            'weight' => $wooProduct['weight'] ?? null,
            'dimensions' => [
                'length' => $wooProduct['dimensions']['length'] ?? null,
                'width' => $wooProduct['dimensions']['width'] ?? null,
                'height' => $wooProduct['dimensions']['height'] ?? null,
            ],
            'woocommerce_id' => $wooProduct['id'],
            'status' => $wooProduct['status'] ?? 'draft',
            'featured' => $wooProduct['featured'] ?? false,
            'catalog_visibility' => $wooProduct['catalog_visibility'] ?? 'visible',
            'images' => $wooProduct['images'] ?? [],
            'meta_data' => $wooProduct['meta_data'] ?? [],
        ];
    }
}
