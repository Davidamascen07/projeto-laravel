<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Shop Controller
 *
 * Controller para páginas web da loja virtual
 * Demonstra: Frontend views, API integration
 */
class ShopController extends Controller
{
    /**
     * Página principal da loja
     */
    public function index()
    {
        return view('shop.index', [
            'title' => 'Loja Virtual - Produtos',
            'description' => 'E-commerce Laravel com integração WooCommerce - Demonstração técnica'
        ]);
    }

    /**
     * Página de produto individual
     */
    public function show($id)
    {
        return view('shop.product', [
            'productId' => $id,
            'title' => 'Detalhes do Produto',
            'description' => 'Visualizar detalhes do produto'
        ]);
    }

    /**
     * Página de categoria
     */
    public function category($slug)
    {
        return view('shop.category', [
            'categorySlug' => $slug,
            'title' => 'Categoria - ' . ucfirst($slug),
            'description' => 'Produtos da categoria ' . $slug
        ]);
    }

    /**
     * Carrinho de compras
     */
    public function cart()
    {
        return view('shop.cart', [
            'title' => 'Carrinho de Compras',
            'description' => 'Seus produtos selecionados'
        ]);
    }

    /**
     * Página de checkout
     */
    public function checkout()
    {
        return view('shop.checkout', [
            'title' => 'Finalizar Compra',
            'description' => 'Finalize sua compra com segurança'
        ]);
    }
}
