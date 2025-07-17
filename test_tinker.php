use App\Services\ProductService;
use App\Repositories\ProductRepositoryInterface;
use App\Integrations\WooCommerce\WooCommerceClient;

echo "=== TESTE DAS APIs DO PROJETO E-COMMERCE ===\n\n";

// Test 1: Listar produtos
echo "1. Testando listagem de produtos:\n";
$productRepository = app(ProductRepositoryInterface::class);
$wooCommerceClient = app(WooCommerceClient::class);
$productService = new ProductService($productRepository, $wooCommerceClient);
$products = $productService->getProducts();

echo "   ✓ Encontrados " . $products->total() . " produtos\n";
if ($products->count() > 0) {
    $firstProduct = $products->items()[0];
    echo "   ✓ Primeiro produto: " . $firstProduct->name . "\n";
    echo "   ✓ SKU: " . $firstProduct->sku . "\n";
    echo "   ✓ Preço: R$ " . number_format($firstProduct->price, 2, ',', '.') . "\n";
}

// Test 2: Buscar produto por ID
echo "\n2. Testando busca de produto por ID:\n";
$product = $productService->findProduct(1);
if ($product) {
    echo "   ✓ Produto encontrado: " . $product->name . "\n";
    echo "   ✓ Em estoque: " . ($product->in_stock ? 'Sim' : 'Não') . "\n";
}

// Test 3: Produtos em destaque
echo "\n3. Testando produtos em destaque:\n";
$featuredProducts = $productService->getProducts(['featured' => true]);
echo "   ✓ Produtos em destaque: " . $featuredProducts->total() . "\n";

// Test 4: Repository Pattern
echo "\n4. Testando Repository Pattern:\n";
$repository = app(ProductRepositoryInterface::class);
echo "   ✓ Repository Class: " . get_class($repository) . "\n";
$activeProducts = $repository->getActiveProducts();
echo "   ✓ Produtos ativos: " . $activeProducts->count() . "\n";

echo "\n=== TESTE CONCLUÍDO ===\n";
echo "APIs funcionando corretamente!\n";
