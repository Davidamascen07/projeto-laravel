<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas RESTful para API do e-commerce
| Demonstra: API Design, Resource Routes, Middleware
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
*/

Route::prefix('products')->group(function () {
    // Rotas públicas (sem autenticação)
    Route::get('/', [ProductController::class, 'index'])
        ->name('api.products.index');

    Route::get('/featured', [ProductController::class, 'featured'])
        ->name('api.products.featured');

    Route::get('/{id}', [ProductController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('api.products.show');

    // Rotas protegidas (requerem autenticação)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [ProductController::class, 'store'])
            ->name('api.products.store');

        Route::put('/{id}', [ProductController::class, 'update'])
            ->where('id', '[0-9]+')
            ->name('api.products.update');

        Route::delete('/{id}', [ProductController::class, 'destroy'])
            ->where('id', '[0-9]+')
            ->name('api.products.destroy');

        Route::patch('/{id}/stock', [ProductController::class, 'updateStock'])
            ->where('id', '[0-9]+')
            ->name('api.products.update-stock');

        Route::post('/sync-woocommerce', [ProductController::class, 'syncWooCommerce'])
            ->name('api.products.sync-woocommerce');
    });
});

/*
|--------------------------------------------------------------------------
| Category Routes
|--------------------------------------------------------------------------
*/

Route::prefix('categories')->group(function () {
    Route::get('/{categoryId}/products', [ProductController::class, 'byCategory'])
        ->where('categoryId', '[0-9]+')
        ->name('api.categories.products');
});

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
    ]);
})->name('api.health');

/*
|--------------------------------------------------------------------------
| API Documentation
|--------------------------------------------------------------------------
*/

Route::get('/docs', function () {
    return response()->json([
        'message' => 'E-commerce API Documentation',
        'version' => '1.0.0',
        'endpoints' => [
            'products' => [
                'GET /api/products' => 'Listar produtos com filtros',
                'GET /api/products/{id}' => 'Exibir produto específico',
                'GET /api/products/featured' => 'Listar produtos em destaque',
                'POST /api/products' => 'Criar novo produto (autenticado)',
                'PUT /api/products/{id}' => 'Atualizar produto (autenticado)',
                'DELETE /api/products/{id}' => 'Remover produto (autenticado)',
                'PATCH /api/products/{id}/stock' => 'Atualizar estoque (autenticado)',
                'POST /api/products/sync-woocommerce' => 'Sincronizar com WooCommerce (autenticado)',
            ],
            'categories' => [
                'GET /api/categories/{id}/products' => 'Listar produtos por categoria',
            ],
            'system' => [
                'GET /api/health' => 'Status da API',
                'GET /api/docs' => 'Esta documentação',
            ]
        ],
        'authentication' => 'Bearer Token (Laravel Sanctum)',
        'rate_limiting' => '60 requests per minute',
    ]);
})->name('api.docs');
