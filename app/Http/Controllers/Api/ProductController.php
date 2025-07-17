<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Product API Controller
 *
 * Controller RESTful para gerenciamento de produtos
 * Demonstra: REST API, Resource Controllers, HTTP Methods
 */
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Lista produtos com filtros
     * GET /api/products
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'status', 'category_id', 'in_stock', 'featured',
            'price_min', 'price_max', 'search', 'sort_by',
            'sort_direction', 'per_page'
        ]);

        $products = $this->productService->getProducts($filters);

        return ProductResource::collection($products);
    }

    /**
     * Exibe produto específico
     * GET /api/products/{id}
     */
    public function show(int $id): ProductResource
    {
        $product = $this->productService->findProduct($id);

        if (!$product) {
            abort(404, 'Produto não encontrado');
        }

        return new ProductResource($product);
    }

    /**
     * Cria novo produto
     * POST /api/products
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return response()->json([
                'message' => 'Produto criado com sucesso',
                'data' => new ProductResource($product)
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => 'Não foi possível criar o produto'
            ], 500);
        }
    }

    /**
     * Atualiza produto existente
     * PUT /api/products/{id}
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());

            return response()->json([
                'message' => 'Produto atualizado com sucesso',
                'data' => new ProductResource($product)
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => 'Não foi possível atualizar o produto'
            ], 500);
        }
    }

    /**
     * Remove produto
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if ($deleted) {
                return response()->json([
                    'message' => 'Produto removido com sucesso'
                ]);
            }

            return response()->json([
                'message' => 'Erro ao remover produto'
            ], 500);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => 'Não foi possível remover o produto'
            ], 500);
        }
    }

    /**
     * Lista produtos em destaque
     * GET /api/products/featured
     */
    public function featured(Request $request): AnonymousResourceCollection
    {
        $limit = $request->get('limit', 10);
        $products = $this->productService->getFeaturedProducts($limit);

        return ProductResource::collection($products);
    }

    /**
     * Atualiza estoque do produto
     * PATCH /api/products/{id}/stock
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'operation' => 'required|in:set,add,subtract'
        ]);

        try {
            $product = $this->productService->updateStock(
                $id,
                $request->quantity,
                $request->operation
            );

            return response()->json([
                'message' => 'Estoque atualizado com sucesso',
                'data' => new ProductResource($product)
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'error' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => 'Não foi possível atualizar o estoque'
            ], 500);
        }
    }

    /**
     * Sincroniza produtos do WooCommerce
     * POST /api/products/sync-woocommerce
     */
    public function syncWooCommerce(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100'
        ]);

        try {
            $results = $this->productService->syncFromWooCommerce(
                $request->get('page', 1),
                $request->get('per_page', 50)
            );

            return response()->json([
                'message' => 'Sincronização concluída',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro na sincronização',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista produtos por categoria
     * GET /api/categories/{categoryId}/products
     */
    public function byCategory(Request $request, int $categoryId): AnonymousResourceCollection
    {
        $filters = $request->only([
            'status', 'in_stock', 'featured', 'price_min', 'price_max',
            'search', 'sort_by', 'sort_direction', 'per_page'
        ]);

        $products = $this->productService->getProductsByCategory($categoryId, $filters);

        return ProductResource::collection($products);
    }
}
