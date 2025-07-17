<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Customer Model
 *
 * Representa um cliente no sistema
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'billing_address',
        'shipping_address',
        'woocommerce_id',
        'meta_data',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'meta_data' => 'array',
        'date_of_birth' => 'date',
    ];

    protected $dates = [
        'date_of_birth',
        'deleted_at',
    ];

    /**
     * Relacionamento com pedidos
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Acessor para nome completo
     */
    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope para buscar por email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }
}
