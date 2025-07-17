<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Product Repository Interface
 *
 * Define o contrato para acesso a dados de produtos
 * Demonstra: Repository Pattern, Interface Segregation
 */
interface ProductRepositoryInterface
{
    /**
     * Buscar produto por ID
     */
    public function findById(int $id): ?Product;

    /**
     * Buscar produto por SKU
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Buscar produto por ID do WooCommerce
     */
    public function findByWooCommerceId(int $wooCommerceId): ?Product;

    /**
     * Verificar se SKU existe
     */
    public function existsBySku(string $sku): bool;

    /**
     * Listar produtos com filtros e paginação
     */
    public function getWithFilters(array $filters = []): LengthAwarePaginator;

    /**
     * Buscar produtos em destaque
     */
    public function getFeatured(int $limit = 10): Collection;

    /**
     * Buscar produtos por categoria
     */
    public function getByCategory(int $categoryId, array $filters = []): LengthAwarePaginator;

    /**
     * Criar produto
     */
    public function create(array $data): Product;

    /**
     * Atualizar produto
     */
    public function update(Product $product, array $data): Product;

    /**
     * Deletar produto
     */
    public function delete(Product $product): bool;

    /**
     * Buscar produtos com estoque baixo
     */
    public function getLowStock(int $threshold = 5): Collection;
}
