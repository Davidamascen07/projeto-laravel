<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Product Request
 *
 * Validação para atualização de produtos
 * Demonstra: Form Request Validation, Conditional Rules
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição
     */
    public function authorize(): bool
    {
        return true; // Implementar lógica de autorização conforme necessário
    }

    /**
     * Regras de validação
     */
    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            'price' => 'sometimes|required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'nullable|integer|min:0',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'status' => 'in:draft,pending,private,publish',
            'featured' => 'boolean',
            'catalog_visibility' => 'in:visible,catalog,search,hidden',
            'images' => 'nullable|array',
            'images.*.src' => 'required_with:images|url',
            'images.*.name' => 'nullable|string',
            'images.*.alt' => 'nullable|string',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'meta_data' => 'nullable|array',
        ];
    }

    /**
     * Mensagens de validação customizadas
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório',
            'name.max' => 'O nome do produto não pode ter mais de 255 caracteres',
            'sku.unique' => 'Este SKU já está sendo usado por outro produto',
            'sku.required' => 'O SKU do produto é obrigatório',
            'price.required' => 'O preço do produto é obrigatório',
            'price.numeric' => 'O preço deve ser um valor numérico',
            'price.min' => 'O preço não pode ser negativo',
            'sale_price.lt' => 'O preço promocional deve ser menor que o preço regular',
            'stock_quantity.integer' => 'A quantidade em estoque deve ser um número inteiro',
            'stock_quantity.min' => 'A quantidade em estoque não pode ser negativa',
            'weight.numeric' => 'O peso deve ser um valor numérico',
            'weight.min' => 'O peso não pode ser negativo',
            'status.in' => 'Status inválido. Use: draft, pending, private ou publish',
            'catalog_visibility.in' => 'Visibilidade inválida. Use: visible, catalog, search ou hidden',
            'images.*.src.url' => 'A URL da imagem deve ser válida',
            'category_ids.*.exists' => 'Uma ou mais categorias selecionadas não existem',
        ];
    }

    /**
     * Preparar dados para validação
     */
    protected function prepareForValidation(): void
    {
        // Converter valores boolean se presentes
        if ($this->has('manage_stock')) {
            $this->merge(['manage_stock' => $this->boolean('manage_stock')]);
        }

        if ($this->has('in_stock')) {
            $this->merge(['in_stock' => $this->boolean('in_stock')]);
        }

        if ($this->has('featured')) {
            $this->merge(['featured' => $this->boolean('featured')]);
        }

        // Se não gerencia estoque, remover quantidade
        if ($this->has('manage_stock') && !$this->boolean('manage_stock')) {
            $this->merge(['stock_quantity' => null]);
        }
    }

    /**
     * Configurar validação após validação passar
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Se está definindo para gerenciar estoque, quantidade é obrigatória
            if ($this->has('manage_stock') &&
                $this->boolean('manage_stock') &&
                is_null($this->input('stock_quantity'))) {
                $validator->errors()->add(
                    'stock_quantity',
                    'A quantidade em estoque é obrigatória quando o controle de estoque está ativo'
                );
            }

            // Se alterando para fora de estoque e gerencia estoque, definir quantidade como 0
            if ($this->has('in_stock') &&
                !$this->boolean('in_stock') &&
                $this->boolean('manage_stock') &&
                ($this->input('stock_quantity') > 0)) {
                $validator->errors()->add(
                    'stock_quantity',
                    'A quantidade deve ser 0 quando o produto está marcado como fora de estoque'
                );
            }
        });
    }
}
