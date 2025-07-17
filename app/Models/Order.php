<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Order Model
 *
 * Representa um pedido no sistema de e-commerce
 * Integra com WooCommerce e sistemas de pagamento
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ON_HOLD = 'on-hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'currency',
        'total',
        'subtotal',
        'tax_total',
        'shipping_total',
        'discount_total',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'billing_address',
        'shipping_address',
        'customer_note',
        'woocommerce_id',
        'meta_data',
        'date_paid',
        'date_completed',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'meta_data' => 'array',
        'date_paid' => 'datetime',
        'date_completed' => 'datetime',
    ];

    protected $dates = [
        'date_paid',
        'date_completed',
        'deleted_at',
    ];

    /**
     * Relacionamento com cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com itens do pedido
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relacionamento com pagamentos
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope para pedidos por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para pedidos pagos
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('date_paid');
    }

    /**
     * Scope para pedidos completos
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Acessor para verificar se pedido está pago
     */
    public function getIsPaidAttribute(): bool
    {
        return !is_null($this->date_paid) &&
               in_array($this->status, [
                   self::STATUS_PROCESSING,
                   self::STATUS_COMPLETED
               ]);
    }

    /**
     * Acessor para verificar se pedido pode ser cancelado
     */
    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_ON_HOLD
        ]);
    }

    /**
     * Acessor para nome do cliente
     */
    public function getCustomerNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }

        $billing = $this->billing_address;
        return $billing['first_name'] . ' ' . $billing['last_name'];
    }

    /**
     * Gerar número único do pedido
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . date('Y') . '-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Calcular total do pedido
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return $subtotal + $this->tax_total + $this->shipping_total - $this->discount_total;
    }

    /**
     * Verificar se todos os itens estão em estoque
     */
    public function hasStockAvailable(): bool
    {
        foreach ($this->items as $item) {
            if (!$item->product->is_available) {
                return false;
            }

            if ($item->product->manage_stock &&
                $item->product->stock_quantity < $item->quantity) {
                return false;
            }
        }

        return true;
    }
}
