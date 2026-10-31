<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white uppercase tracking-wide leading-tight">
            Novo pedido
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div id="order-errors" role="alert" tabindex="-1" class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">
                    <p class="font-semibold">Não foi possível fechar o pedido:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[1fr_24rem] lg:items-start">
                <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5" aria-labelledby="catalogo">
                    <h3 id="catalogo" class="text-lg font-semibold text-gray-900">Cardápio</h3>

                    <div>
                        <x-input-label for="order-search" value="Buscar por código ou nome" />
                        <x-text-input id="order-search" type="search" class="mt-1 block w-full" autocomplete="off" />
                    </div>

                    <p id="order-search-empty" class="hidden text-sm text-gray-600">Nenhum item encontrado.</p>

                    @foreach ($categories as $category)
                        @php($products = $category->products->filter(fn ($product) => $product->variations->isNotEmpty()))
                        @if ($products->isNotEmpty())
                            <div data-order-category class="space-y-2">
                                <h4 class="text-sm font-semibold text-brand-800 uppercase tracking-wide">{{ $category->name }}</h4>

                                <ul role="list" class="divide-y divide-gray-100">
                                    @foreach ($products as $product)
                                        <li data-order-product data-search="{{ Str::lower($product->code.' '.$product->name) }}" class="py-2 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
                                            <span class="text-sm font-medium text-gray-900">
                                                @if ($product->code)
                                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-black bg-gold-400 text-brand-950 font-mono">#{{ $product->code }}</span>
                                                @endif
                                                {{ $product->name }}
                                            </span>

                                            <span class="flex flex-wrap gap-2 sm:justify-end">
                                                @foreach ($product->variations as $variation)
                                                    <button
                                                        type="button"
                                                        data-add-variation
                                                        data-variation-id="{{ $variation->id }}"
                                                        data-label="{{ \App\Models\OrderItem::describe($product->name, $variation->name) }}"
                                                        data-price-cents="{{ (int) round($variation->price * 100) }}"
                                                        aria-label="Adicionar {{ $product->name }} - {{ $variation->name }}, {{ $variation->formatted_price }}"
                                                        class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-brand-200 bg-brand-50 text-brand-900 hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-gold-500"
                                                    >
                                                        {{ $variation->name }} · {{ $variation->formatted_price }}
                                                    </button>
                                                @endforeach
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </section>

                <form
                    id="order-form"
                    method="POST"
                    action="{{ route('orders.store') }}"
                    data-cash-method="{{ $cashPaymentMethod }}"
                    data-old-items='@json(array_values(old('items', [])))'
                    class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5 lg:sticky lg:top-4"
                    aria-labelledby="comanda"
                >
                    @csrf

                    <h3 id="comanda" class="text-lg font-semibold text-gray-900">Comanda</h3>

                    <p id="order-empty" class="text-sm text-gray-600">Nenhum item adicionado.</p>
                    <ul id="order-lines" role="list" class="divide-y divide-gray-100"></ul>

                    <p class="flex items-baseline justify-between text-gray-900 border-t border-gray-200 pt-3">
                        <span class="font-semibold">Total</span>
                        <output id="order-total" aria-live="polite" class="text-xl font-bold text-brand-800">R$ 0,00</output>
                    </p>

                    <div>
                        <x-input-label for="payment_method" value="Forma de pagamento" />
                        <select id="payment_method" name="payment_method" required class="mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm" aria-describedby="payment_method-error">
                            @foreach ($paymentMethods as $code => $name)
                                <option value="{{ $code }}" @selected(old('payment_method', $cashPaymentMethod) === (string) $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <x-input-error id="payment_method-error" class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>

                    <div id="cash-fields" class="space-y-3">
                        <div>
                            <x-input-label for="amount_received" value="Valor recebido (R$)" />
                            <x-text-input id="amount_received" name="amount_received" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('amount_received')" aria-describedby="amount_received-error" />
                            <x-input-error id="amount_received-error" class="mt-2" :messages="$errors->get('amount_received')" />
                        </div>
                        <p class="flex items-baseline justify-between text-sm text-gray-700">
                            <span>Troco</span>
                            <output id="order-change" aria-live="polite" class="font-semibold">R$ 0,00</output>
                        </p>
                    </div>

                    <div>
                        <x-input-label for="consumer_cpf" value="CPF na nota (opcional)" />
                        <x-text-input id="consumer_cpf" name="consumer_cpf" type="text" inputmode="numeric" maxlength="14" class="mt-1 block w-full" :value="old('consumer_cpf')" aria-describedby="consumer_cpf-error" />
                        <x-input-error id="consumer_cpf-error" class="mt-2" :messages="$errors->get('consumer_cpf')" />
                    </div>

                    <x-primary-button class="w-full justify-center">Fechar pedido</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
