<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Os códigos fiscais são gravados só com dígitos, como o XML exige.
     */
    protected function prepareForValidation(): void
    {
        $digits = fn (string $field) => $this->filled($field)
            ? preg_replace('/\D/', '', (string) $this->input($field))
            : null;

        $this->merge([
            'ncm' => $digits('ncm'),
            'cfop' => $digits('cfop'),
            'csosn' => $digits('csosn'),
            'cest' => $digits('cest'),
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'code' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'ncm' => ['required', 'regex:/^\d{8}$/'],
            'cfop' => ['required', 'regex:/^\d{4}$/'],
            'csosn' => ['required', 'regex:/^\d{3}$/'],
            // CEST existe só para itens com substituição tributária (CSOSN 500).
            'cest' => ['nullable', 'regex:/^\d{7}$/', 'required_if:csosn,500', 'prohibited_unless:csosn,500'],
            'unit' => ['required', 'string', 'max:6'],
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.id' => ['nullable', 'integer', Rule::in($product instanceof Product ? $product->variations->pluck('id') : [])],
            'variations.*.name' => ['required', 'string', 'max:255'],
            'variations.*.price' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999.99'],
            'variations.*.is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'categoria',
            'code' => 'código',
            'name' => 'nome',
            'description' => 'descrição',
            'is_active' => 'ativo',
            'ncm' => 'NCM',
            'cfop' => 'CFOP',
            'csosn' => 'CSOSN',
            'cest' => 'CEST',
            'unit' => 'unidade',
            'variations' => 'variações',
            'variations.*.id' => 'variação',
            'variations.*.name' => 'nome da variação',
            'variations.*.price' => 'preço da variação',
            'variations.*.is_active' => 'variação ativa',
        ];
    }
}
