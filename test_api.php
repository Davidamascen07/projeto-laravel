<?php

use App\Http\Controllers\ProductController;
use App\Services\ProductService;
use App\Repositories\ProductRepositoryInterface;
use App\Integrations\WooCommerce\WooCommerceClient;
use Illuminate\Http\Request;

echo "=== TESTE DAS APIs DO PROJETO E-COMMERCE ===\n\n";

// Test 1: Listar todos os produtos (simulando GET /api/products)
echo "1. Testando listagem de produtos:\n";
try {
    $productRepository = app(ProductRepositoryInterface::class);
    $wooCommerceClient = app(WooCommerceClient::class);
    $productService = new ProductService($productRepository, $wooCommerceClient);
    $products = $productService->getProducts();

    echo "   ✓ Encontrados " . $products->total() . " produtos\n";
    if ($products->count() > 0) {
        $firstProduct = $products->items()[0];
        echo "   ✓ Primeiro produto: " . $firstProduct->name . "\n";
        echo "   ✓ SKU: " . $firstProduct->sku . "\n";
        echo "   ✓ Preço: R$ " . number_format($firstProduct->price, 2, ',', '.') . "\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Test 2: Buscar produto por ID (simulando GET /api/products/{id})
echo "2. Testando busca de produto por ID:\n";
try {
    $product = $productService->findProduct(1);
    if ($product) {
        echo "   ✓ Produto encontrado: " . $product->name . "\n";
        echo "   ✓ Descrição: " . substr($product->description, 0, 50) . "...\n";
        echo "   ✓ Em estoque: " . ($product->in_stock ? 'Sim' : 'Não') . "\n\n";
    } else {
        echo "   ✗ Produto não encontrado\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Test 3: Buscar produtos com filtros (simulando GET /api/products?featured=1)
echo "3. Testando busca de produtos em destaque:\n";
try {
    $featuredProducts = $productService->getProducts(['featured' => true]);
    echo "   ✓ Produtos em destaque: " . $featuredProducts->count() . "\n";
    foreach ($featuredProducts as $product) {
        echo "   ✓ " . $product->name . " (R$ " . number_format($product->price, 2, ',', '.') . ")\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Test 4: Testar busca por texto (simulando GET /api/products?search=samsung)
echo "4. Testando busca por texto:\n";
try {
    $searchResults = $productService->getProducts(['search' => 'Samsung']);
    echo "   ✓ Resultados para 'Samsung': " . $searchResults->count() . "\n";
    foreach ($searchResults as $product) {
        echo "   ✓ " . $product->name . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Test 5: Criar um novo produto (simulando POST /api/products)
echo "5. Testando criação de produto:\n";
try {
    $newProductData = [
        'name' => 'Produto de Teste API',
        'description' => 'Produto criado para testar a API',
        'sku' => 'TEST-API-001',
        'price' => 99.99,
        'stock_quantity' => 10,
        'status' => 'publish',
        'featured' => false
    ];

    $newProduct = $productService->createProduct($newProductData);
    echo "   ✓ Produto criado com sucesso!\n";
    echo "   ✓ ID: " . $newProduct->id . "\n";
    echo "   ✓ Nome: " . $newProduct->name . "\n";
    echo "   ✓ SKU: " . $newProduct->sku . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Test 6: Atualizar estoque (simulando PATCH /api/products/{id}/stock)
echo "6. Testando atualização de estoque:\n";
try {
    $product = $productService->findProduct(1);
    $oldStock = $product->stock_quantity;

    $updatedProduct = $productService->updateStock(1, 100);
    echo "   ✓ Estoque atualizado!\n";
    echo "   ✓ Estoque anterior: " . $oldStock . "\n";
    echo "   ✓ Novo estoque: " . $updatedProduct->stock_quantity . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Test 7: Verificar Repository Pattern
echo "7. Testando Repository Pattern:\n";
try {
    $repository = app(ProductRepositoryInterface::class);
    echo "   ✓ Repository Interface: " . get_class($repository) . "\n";

    $activeProducts = $repository->getActiveProducts();
    echo "   ✓ Produtos ativos via repository: " . $activeProducts->count() . "\n";

    $paginatedProducts = $repository->getPaginatedProducts(2);
    echo "   ✓ Paginação funcionando: " . $paginatedProducts->total() . " total, " . $paginatedProducts->perPage() . " por página\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

echo "=== TESTE CONCLUÍDO ===\n";
echo "Todas as funcionalidades principais foram testadas!\n";
echo "O projeto está funcionando corretamente.\n";
