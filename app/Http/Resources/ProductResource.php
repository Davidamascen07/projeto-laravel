<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product Resource
 *
 * Transforma modelo Product para resposta de API
 * Demonstra: API Resources, Data Transformation
 */
class ProductResource extends JsonResource
{
    /**
     * Transforma o resource em array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'sku' => $this->sku,
            'slug' => $this->slug ?? str($this->name)->slug(),

            // Preços
            'price' => [
                'regular' => (float) $this->price,
                'sale' => $this->sale_price ? (float) $this->sale_price : null,
                'effective' => (float) $this->effective_price,
                'currency' => 'BRL',
                'formatted' => [
                    'regular' => 'R$ ' . number_format($this->price, 2, ',', '.'),
                    'sale' => $this->sale_price ? 'R$ ' . number_format($this->sale_price, 2, ',', '.') : null,
                    'effective' => 'R$ ' . number_format($this->effective_price, 2, ',', '.'),
                ]
            ],

            // Estoque
            'stock' => [
                'manage_stock' => $this->manage_stock,
                'quantity' => $this->manage_stock ? $this->stock_quantity : null,
                'in_stock' => $this->in_stock,
                'is_available' => $this->is_available,
                'status' => $this->getStockStatus(),
            ],

            // Dimensões e peso
            'shipping' => [
                'weight' => $this->weight,
                'dimensions' => $this->dimensions,
                'weight_unit' => 'kg',
                'dimension_unit' => 'cm',
            ],

            // Status e visibilidade
            'status' => $this->status,
            'featured' => $this->featured,
            'catalog_visibility' => $this->catalog_visibility,

            // Imagens
            'images' => $this->formatImages(),
            'main_image' => $this->main_image,

            // Categorias (quando carregadas)
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),

            // Relacionado ao WooCommerce
            'woocommerce_id' => $this->woocommerce_id,
            'sync_status' => $this->getSyncStatus(),

            // Meta dados
            'meta_data' => $this->meta_data,

            // URLs
            'urls' => [
                'view' => url("/produtos/{$this->id}"),
                'edit' => url("/admin/produtos/{$this->id}/editar"),
                'api' => url("/api/products/{$this->id}"),
            ],

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Informações adicionais quando solicitadas
            'statistics' => $this->when(
                $request->input('include') === 'statistics',
                function () {
                    return [
                        'total_orders' => $this->orderItems()->count(),
                        'total_sold' => $this->orderItems()->sum('quantity'),
                        'revenue' => $this->orderItems()->sum('total'),
                    ];
                }
            ),
        ];
    }

    /**
     * Formatar imagens do produto
     */
    private function formatImages(): array
    {
        if (!$this->images || !is_array($this->images)) {
            return [];
        }

        return collect($this->images)->map(function ($image, $index) {
            return [
                'id' => $image['id'] ?? $index,
                'src' => $image['src'],
                'name' => $image['name'] ?? '',
                'alt' => $image['alt'] ?? $this->name,
                'position' => $index,
                'is_main' => $index === 0,
            ];
        })->toArray();
    }

    /**
     * Obter status do estoque formatado
     */
    private function getStockStatus(): string
    {
        if (!$this->in_stock) {
            return 'out_of_stock';
        }

        if (!$this->manage_stock) {
            return 'in_stock';
        }

        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= 5) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Obter status de sincronização com WooCommerce
     */
    private function getSyncStatus(): string
    {
        if (!$this->woocommerce_id) {
            return 'not_synced';
        }

        // Aqui poderia verificar se produto precisa ser sincronizado
        // comparando updated_at com última sincronização
        return 'synced';
    }
}
