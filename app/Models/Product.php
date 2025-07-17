<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Product Model
 *
 * Representa um produto no sistema de e-commerce
 * Integra com WooCommerce via API
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'sku',
        'price',
        'sale_price',
        'stock_quantity',
        'manage_stock',
        'in_stock',
        'weight',
        'dimensions',
        'woocommerce_id',
        'status',
        'featured',
        'catalog_visibility',
        'meta_data',
        'images',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'weight' => 'decimal:2',
        'manage_stock' => 'boolean',
        'in_stock' => 'boolean',
        'featured' => 'boolean',
        'dimensions' => 'array',
        'meta_data' => 'array',
        'images' => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Relacionamento com categorias
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Relacionamento com itens de pedido
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope para produtos em estoque
     */
    public function scopeInStock($query)
    {
        return $query->where('in_stock', true)
                    ->where(function ($q) {
                        $q->where('manage_stock', false)
                          ->orWhere(function ($qq) {
                              $qq->where('manage_stock', true)
                                 ->where('stock_quantity', '>', 0);
                          });
                    });
    }

    /**
     * Scope para produtos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'publish');
    }

    /**
     * Scope para produtos em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Acessor para verificar se produto está disponível
     */
    public function getIsAvailableAttribute(): bool
    {
        if (!$this->in_stock || $this->status !== 'publish') {
            return false;
        }

        if ($this->manage_stock && $this->stock_quantity <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Acessor para preço com desconto
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price && $this->sale_price > 0
            ? $this->sale_price
            : $this->price;
    }

    /**
     * Acessor para imagem principal
     */
    public function getMainImageAttribute(): ?string
    {
        return $this->images[0]['src'] ?? null;
    }

    /**
     * Mutator para SKU em maiúsculo
     */
    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = strtoupper($value);
    }
}
