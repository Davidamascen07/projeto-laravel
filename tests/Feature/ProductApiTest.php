<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Product API Test
 *
 * Testes de integração para API de produtos
 * Demonstra: Feature Tests, API Testing, Authentication
 */
class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar banco de teste
        $this->artisan('migrate');
    }

    /**
     * Teste: Listar produtos sem autenticação
     */
    public function test_can_list_products_without_authentication(): void
    {
        // Criar produtos de teste
        Product::factory()->count(5)->create([
            'status' => 'publish',
            'in_stock' => true
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'sku',
                            'price',
                            'stock',
                            'status'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total'
                    ]
                ]);
    }

    /**
     * Teste: Visualizar produto específico
     */
    public function test_can_show_specific_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Produto Teste',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'status' => 'publish'
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $product->id,
                        'name' => 'Produto Teste',
                        'sku' => 'TEST-001'
                    ]
                ]);
    }

    /**
     * Teste: Produto não encontrado retorna 404
     */
    public function test_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/products/999999');

        $response->assertStatus(404);
    }

    /**
     * Teste: Criar produto requer autenticação
     */
    public function test_creating_product_requires_authentication(): void
    {
        $productData = [
            'name' => 'Novo Produto',
            'sku' => 'NEW-001',
            'price' => 149.99
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(401);
    }

    /**
     * Teste: Criar produto com autenticação
     */
    public function test_can_create_product_when_authenticated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $productData = [
            'name' => 'Produto Autenticado',
            'sku' => 'AUTH-001',
            'price' => 199.99,
            'description' => 'Descrição do produto',
            'manage_stock' => true,
            'stock_quantity' => 50
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Produto criado com sucesso',
                    'data' => [
                        'name' => 'Produto Autenticado',
                        'sku' => 'AUTH-001'
                    ]
                ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Produto Autenticado',
            'sku' => 'AUTH-001',
            'price' => 199.99
        ]);
    }

    /**
     * Teste: Validação de dados obrigatórios
     */
    public function test_validates_required_fields_when_creating_product(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'price']);
    }

    /**
     * Teste: SKU deve ser único
     */
    public function test_sku_must_be_unique(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Product::factory()->create(['sku' => 'DUPLICATE-SKU']);

        $productData = [
            'name' => 'Produto Duplicado',
            'sku' => 'DUPLICATE-SKU',
            'price' => 99.99
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Teste: Atualizar produto
     */
    public function test_can_update_product_when_authenticated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create([
            'name' => 'Produto Original',
            'price' => 100.00
        ]);

        $updateData = [
            'name' => 'Produto Atualizado',
            'price' => 150.00
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Produto atualizado com sucesso',
                    'data' => [
                        'name' => 'Produto Atualizado'
                    ]
                ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produto Atualizado',
            'price' => 150.00
        ]);
    }

    /**
     * Teste: Deletar produto
     */
    public function test_can_delete_product_when_authenticated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Produto removido com sucesso'
                ]);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /**
     * Teste: Listar produtos em destaque
     */
    public function test_can_list_featured_products(): void
    {
        Product::factory()->count(3)->create(['featured' => true, 'status' => 'publish']);
        Product::factory()->count(5)->create(['featured' => false, 'status' => 'publish']);

        $response = $this->getJson('/api/products/featured');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        foreach ($data as $product) {
            $this->assertTrue($product['featured']);
        }
    }

    /**
     * Teste: Filtrar produtos por categoria
     */
    public function test_can_filter_products_by_category(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create(['status' => 'publish']);

        // Associar produtos à categoria
        foreach ($products as $product) {
            $product->categories()->attach($category->id);
        }

        $response = $this->getJson("/api/categories/{$category->id}/products");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    /**
     * Teste: Atualizar estoque do produto
     */
    public function test_can_update_product_stock(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 100
        ]);

        $response = $this->patchJson("/api/products/{$product->id}/stock", [
            'quantity' => 50,
            'operation' => 'subtract'
        ]);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertEquals(50, $product->stock_quantity);
    }

    /**
     * Teste: Health check da API
     */
    public function test_api_health_check(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'timestamp',
                    'version',
                    'environment'
                ])
                ->assertJson([
                    'status' => 'ok'
                ]);
    }
}
