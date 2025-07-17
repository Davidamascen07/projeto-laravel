<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Criar produtos de eletrônicos
        Product::create([
            'name' => 'Smartphone Galaxy S23',
            'description' => 'Smartphone premium com câmera de alta qualidade e performance excepcional.',
            'price' => 2999.99,
            'sale_price' => 2599.99,
            'sku' => 'PHONE-001',
            'stock_quantity' => 25,
            'status' => 'publish',
            'featured' => true,
            'woocommerce_id' => null,
            'images' => [
                [
                    'src' => '/img/teste.jpg',
                    'alt' => 'Smartphone Galaxy S23',
                    'name' => 'smartphone-galaxy-s23.jpg'
                ]
            ],
            'meta_data' => json_encode([
                'brand' => 'Samsung',
                'color' => 'Preto',
                'storage' => '256GB'
            ])
        ]);

        Product::create([
            'name' => 'Notebook Dell Inspiron',
            'description' => 'Notebook para trabalho e estudos com processador Intel i5.',
            'price' => 3499.99,
            'sale_price' => null,
            'sku' => 'LAPTOP-001',
            'stock_quantity' => 15,
            'status' => 'publish',
            'featured' => false,
            'woocommerce_id' => null,
            'images' => [
                [
                    'src' => '/img/notebook.jpg',
                    'alt' => 'Notebook Dell Inspiron',
                    'name' => 'notebook-dell-inspiron.jpg'
                ]
            ],
            'meta_data' => json_encode([
                'brand' => 'Dell',
                'processor' => 'Intel i5',
                'ram' => '8GB'
            ])
        ]);

        // Criar produtos de roupas
        Product::create([
            'name' => 'Camiseta Polo Masculina',
            'description' => 'Camiseta polo de algodão premium, confortável e elegante.',
            'price' => 89.90,
            'sale_price' => 69.90,
            'sku' => 'SHIRT-001',
            'stock_quantity' => 50,
            'status' => 'publish',
            'featured' => true,
            'woocommerce_id' => null,
            'images' => [
                [
                    'src' => '/img/camisetaPolo.jpg',
                    'alt' => 'Camiseta Polo Masculina',
                    'name' => 'camiseta-polo-masculina.jpg'
                ]
            ],
            'meta_data' => json_encode([
                'material' => 'Algodão',
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Azul', 'Branco', 'Preto']
            ])
        ]);

        Product::create([
            'name' => 'Calça Jeans Feminina',
            'description' => 'Calça jeans skinny de alta qualidade com elastano.',
            'price' => 159.90,
            'sale_price' => null,
            'sku' => 'JEANS-001',
            'stock_quantity' => 30,
            'status' => 'publish',
            'featured' => false,
            'woocommerce_id' => null,
            'images' => [
                [
                    'src' => '/img/CalçaFeminina.jpg',
                    'alt' => 'Calça Jeans Feminina',
                    'name' => 'calca-jeans-feminina.jpg'
                ]
            ],
            'meta_data' => json_encode([
                'material' => 'Algodão + Elastano',
                'sizes' => ['36', '38', '40', '42'],
                'fit' => 'Skinny'
            ])
        ]);

        // Criar produtos de livros
        Product::create([
            'name' => 'Clean Code - Robert Martin',
            'description' => 'Guia essencial para escrever código limpo e manutenível.',
            'price' => 79.90,
            'sale_price' => 59.90,
            'sku' => 'BOOK-001',
            'stock_quantity' => 100,
            'status' => 'publish',
            'featured' => true,
            'woocommerce_id' => null,
            'images' => [
                [
                    'src' => '/img/CleanCode.jpg',
                    'alt' => 'Livro Clean Code - Robert Martin',
                    'name' => 'clean-code-book.jpg'
                ]
            ],
            'meta_data' => json_encode([
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'pages' => 464,
                'language' => 'Português'
            ])
        ]);

        Product::create([
            'name' => 'Laravel: Up & Running',
            'description' => 'Guia completo para desenvolvimento web com Laravel.',
            'price' => 99.90,
            'sale_price' => null,
            'sku' => 'BOOK-002',
            'stock_quantity' => 75,
            'status' => 'publish',
            'featured' => false,
            'woocommerce_id' => null,
            'images' => [
                [
                    'src' => '/img/Laravel Up and Running .jpg',
                    'alt' => 'Livro Laravel: Up & Running',
                    'name' => 'laravel-up-running-book.jpg'
                ]
            ],
            'meta_data' => json_encode([
                'author' => 'Matt Stauffer',
                'publisher' => "O'Reilly",
                'pages' => 496,
                'language' => 'Inglês'
            ])
        ]);

        // Produto inativo para teste
        Product::create([
            'name' => 'Produto Descontinuado',
            'description' => 'Este produto não está mais disponível.',
            'price' => 199.90,
            'sale_price' => null,
            'sku' => 'DISC-001',
            'stock_quantity' => 0,
            'status' => 'draft',
            'featured' => false,
            'woocommerce_id' => null,
            'meta_data' => json_encode([])
        ]);
    }
}
