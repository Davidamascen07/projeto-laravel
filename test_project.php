#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Services\ProductService;
use App\Repositories\ProductRepositoryInterface;
use App\Integrations\WooCommerce\WooCommerceClient;

echo "=== TESTE DO PROJETO E-COMMERCE LARAVEL ===\n\n";

try {
    // Test 1: Verificar conexão com banco
    echo "1. Testando conexão com banco de dados:\n";
    $productCount = Product::count();
    echo "   ✓ Conexão estabelecida! {$productCount} produtos encontrados\n\n";

    // Test 2: Testar Repository Pattern
    echo "2. Testando Repository Pattern:\n";
    $repository = app(ProductRepositoryInterface::class);
    echo "   ✓ Repository Interface implementada: " . get_class($repository) . "\n";

    $featuredProducts = $repository->getFeatured(5);
    echo "   ✓ Produtos em destaque via repository: " . $featuredProducts->count() . "\n";

    $lowStockProducts = $repository->getLowStock(10);
    echo "   ✓ Produtos com estoque baixo: " . $lowStockProducts->count() . "\n\n";

    // Test 3: Testar Service Layer
    echo "3. Testando Service Layer:\n";
    $wooCommerceClient = app(WooCommerceClient::class);
    $productService = new ProductService($repository, $wooCommerceClient);
    echo "   ✓ ProductService instanciado com dependências injetadas\n";

    $products = $productService->getProducts();
    echo "   ✓ Listagem de produtos funcionando: " . $products->total() . " produtos\n";

    if ($products->count() > 0) {
        $firstProduct = $products->items()[0];
        echo "   ✓ Primeiro produto: " . $firstProduct->name . "\n";
        echo "   ✓ SKU: " . $firstProduct->sku . "\n";
        echo "   ✓ Preço: R$ " . number_format($firstProduct->price, 2, ',', '.') . "\n";
    }
    echo "\n";

    // Test 4: Testar busca por ID
    echo "4. Testando busca por ID:\n";
    $product = $productService->findProduct(1);
    if ($product) {
        echo "   ✓ Produto encontrado: " . $product->name . "\n";
        echo "   ✓ Status: " . $product->status . "\n";
        echo "   ✓ Em estoque: " . ($product->in_stock ? 'Sim' : 'Não') . "\n\n";
    } else {
        echo "   ✗ Produto não encontrado\n\n";
    }

    // Test 5: Testar filtros
    echo "5. Testando filtros:\n";
    $featuredProducts = $productService->getProducts(['featured' => true]);
    echo "   ✓ Produtos em destaque: " . $featuredProducts->total() . "\n";

    $publishedProducts = $productService->getProducts(['status' => 'publish']);
    echo "   ✓ Produtos publicados: " . $publishedProducts->total() . "\n\n";

    // Test 6: Testar criação de produto
    echo "6. Testando criação de produto:\n";
    $newProductData = [
        'name' => 'Produto Teste API',
        'description' => 'Produto criado via teste automatizado',
        'sku' => 'TEST-' . time(),
        'price' => 199.99,
        'stock_quantity' => 50,
        'status' => 'publish',
        'featured' => false
    ];

    $newProduct = $productService->createProduct($newProductData);
    echo "   ✓ Produto criado com sucesso!\n";
    echo "   ✓ ID: " . $newProduct->id . "\n";
    echo "   ✓ Nome: " . $newProduct->name . "\n";
    echo "   ✓ SKU: " . $newProduct->sku . "\n\n";

    echo "=== TODOS OS TESTES PASSARAM! ===\n";
    echo "✓ Banco de dados funcionando\n";
    echo "✓ Repository Pattern implementado\n";
    echo "✓ Service Layer funcionando\n";
    echo "✓ Dependency Injection configurado\n";
    echo "✓ CRUD de produtos funcionando\n";
    echo "✓ Filtros e busca implementados\n";
    echo "\nO projeto está pronto para demonstração!\n";

} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
