<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Product Repository
 *
 * Implementação concreta do repositório de produtos
 * Demonstra: Repository Pattern, Eloquent ORM
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Buscar produto por ID
     */
    public function findById(int $id): ?Product
    {
        return Product::with(['categories'])->find($id);
    }

    /**
     * Buscar produto por SKU
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Buscar produto por ID do WooCommerce
     */
    public function findByWooCommerceId(int $wooCommerceId): ?Product
    {
        return Product::where('woocommerce_id', $wooCommerceId)->first();
    }

    /**
     * Verificar se SKU existe
     */
    public function existsBySku(string $sku): bool
    {
        return Product::where('sku', $sku)->exists();
    }

    /**
     * Listar produtos com filtros e paginação
     */
    public function getWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['categories']);

        // Filtro por status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->active();
        }

        // Filtro por categoria
        if (isset($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        // Filtro por estoque
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->inStock();
        }

        // Filtro por destaque
        if (isset($filters['featured']) && $filters['featured']) {
            $query->featured();
        }

        // Filtro por preço
        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Busca por termo
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Buscar produtos em destaque
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return Product::featured()
                     ->active()
                     ->inStock()
                     ->limit($limit)
                     ->get();
    }

    /**
     * Buscar produtos por categoria
     */
    public function getByCategory(int $categoryId, array $filters = []): LengthAwarePaginator
    {
        $filters['category_id'] = $categoryId;
        return $this->getWithFilters($filters);
    }

    /**
     * Criar produto
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Atualizar produto
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh(['categories']);
    }

    /**
     * Deletar produto
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Buscar produtos com estoque baixo
     */
    public function getLowStock(int $threshold = 5): Collection
    {
        return Product::where('manage_stock', true)
                     ->where('stock_quantity', '<=', $threshold)
                     ->where('stock_quantity', '>', 0)
                     ->active()
                     ->get();
    }
}
