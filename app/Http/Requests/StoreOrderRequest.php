<?php

namespace App\Http\Requests;

use App\Models\OrderItem;
use App\Models\ProductVariation;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'consumer_cpf' => $this->filled('consumer_cpf')
                ? preg_replace('/\D/', '', (string) $this->input('consumer_cpf'))
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.variation_id' => ['required', 'integer', Rule::exists('product_variations', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'payment_method' => ['required', Rule::in(array_keys(config('fiscal.payment_methods')))],
            'amount_received' => ['nullable', 'required_if:payment_method,'.config('fiscal.cash_payment_method'), 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'consumer_cpf' => ['nullable', new Cpf],
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'itens do pedido',
            'items.*.variation_id' => 'item do pedido',
            'items.*.quantity' => 'quantidade',
            'payment_method' => 'forma de pagamento',
            'amount_received' => 'valor recebido',
            'consumer_cpf' => 'CPF',
        ];
    }

    /**
     * Regras que dependem dos preços gravados no banco.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $items = $this->orderItems();

                if ($items->count() !== collect($this->input('items'))->pluck('variation_id')->unique()->count()) {
                    $validator->errors()->add('items', 'Um dos itens não está mais disponível no cardápio.');

                    return;
                }

                if ($this->isCash() && $this->toCents($this->input('amount_received')) < $this->toCents($this->total())) {
                    $validator->errors()->add('amount_received', 'O valor recebido é menor que o total do pedido.');
                }
            },
        ];
    }

    /**
     * Itens do pedido, com preço e dados relidos do banco: o navegador só informa
     * o que foi pedido e quantos. Variação repetida soma as quantidades.
     *
     * @return Collection<int, OrderItem>
     */
    public function orderItems(): Collection
    {
        $quantities = collect($this->input('items'))
            ->groupBy('variation_id')
            ->map(fn ($rows) => $rows->sum('quantity'));

        return ProductVariation::active()
            ->whereHas('product', fn ($query) => $query->active())
            ->with('product')
            ->findMany($quantities->keys())
            ->map(fn ($variation) => OrderItem::fromVariation($variation, (int) $quantities[$variation->id]))
            ->values();
    }

    public function total(): float
    {
        return round($this->orderItems()->sum(fn ($item) => (float) $item->total), 2);
    }

    public function isCash(): bool
    {
        return $this->input('payment_method') === config('fiscal.cash_payment_method');
    }

    private function toCents(mixed $value): int
    {
        return (int) round((float) $value * 100);
    }
}
