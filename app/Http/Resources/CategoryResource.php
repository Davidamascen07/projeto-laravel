<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Category Resource
 *
 * Transforma modelo Category para resposta de API
 */
class CategoryResource extends JsonResource
{
    /**
     * Transforma o resource em array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'woocommerce_id' => $this->woocommerce_id,
            'display_order' => $this->display_order,
            'product_count' => $this->whenCounted('products'),
            'url' => $this->url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
