<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->boolean('manage_stock')->default(false);
            $table->boolean('in_stock')->default(true);
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->unsignedBigInteger('woocommerce_id')->nullable()->unique();
            $table->enum('status', ['draft', 'pending', 'private', 'publish'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->enum('catalog_visibility', ['visible', 'catalog', 'search', 'hidden'])->default('visible');
            $table->json('meta_data')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'featured']);
            $table->index(['in_stock', 'manage_stock']);
            $table->index('woocommerce_id');
            $table->index('sku');
            // $table->fullText(['name', 'description', 'sku']); // SQLite não suporta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
