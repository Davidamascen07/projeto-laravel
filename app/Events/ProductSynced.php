<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Product Synced Event
 *
 * Evento disparado quando produto é sincronizado
 * Demonstra: Events, Domain Events
 */
class ProductSynced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public string $action
    ) {}
}
