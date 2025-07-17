<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Category Model
 *
 * Representa uma categoria de produto
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'woocommerce_id',
        'display_order',
        'meta_data',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'meta_data' => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Relacionamento com produtos
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    /**
     * Scope para categorias principais
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Acessor para URL da categoria
     */
    public function getUrlAttribute(): string
    {
        return '/categoria/' . $this->slug;
    }
}
