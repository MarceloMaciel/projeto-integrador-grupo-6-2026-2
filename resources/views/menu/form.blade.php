@php
    $profileLabels = [
        'prepared' => 'Preparado na casa',
        'soft_drink_pet' => 'Refrigerante em PET',
        'soft_drink_can' => 'Refrigerante em lata',
        'beer_returnable_bottle' => 'Cerveja em garrafa retornável',
        'beer_can' => 'Cerveja em lata',
        'water_bottle' => 'Água em garrafa descartável',
        'juice_can' => 'Suco/néctar em lata',
    ];

    // O perfil não é coluna: é o que tiver os mesmos códigos do produto.
    $currentCodes = [
        'ncm' => old('ncm', $product->ncm),
        'cfop' => old('cfop', $product->cfop),
        'csosn' => old('csosn', $product->csosn),
        'cest' => old('cest', $product->cest),
        'unit' => old('unit', $product->unit),
    ];
    $currentProfile = collect($fiscalProfiles)->search(fn ($profile) => $profile == $currentCodes) ?: '';

    $variationRows = old('variations', $product->variations->map(fn ($variation) => [
        'id' => $variation->id,
        'name' => $variation->name,
        'price' => $variation->price,
        'is_active' => $variation->is_active,
    ])->all());

    if ($variationRows === []) {
        $variationRows = [['id' => null, 'name' => '', 'price' => '', 'is_active' => true]];
    }

    $fieldClass = 'mt-1 block w-full';
    $selectClass = 'mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white uppercase tracking-wide leading-tight">
            {{ $product->exists ? 'Editar produto' : 'Novo produto' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('error'))
                <div role="alert" class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ $product->exists ? route('menu.products.update', $product) : route('menu.products.store') }}" class="space-y-6">
                @csrf
                @if ($product->exists)
                    @method('PUT')
                @endif

                <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4" aria-labelledby="dados-produto">
                    <h3 id="dados-produto" class="text-lg font-semibold text-gray-900">Produto</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="category_id" value="Categoria" />
                            <select id="category_id" name="category_id" required class="{{ $selectClass }}" @error('category_id') aria-invalid="true" aria-describedby="category_id-error" @enderror>
                                <option value="">Selecione</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error id="category_id-error" class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        <div>
                            <x-input-label for="code" value="Código no cardápio (opcional)" />
                            <x-text-input id="code" name="code" type="text" maxlength="20" class="{{ $fieldClass }}" :value="old('code', $product->code)" />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" type="text" required class="{{ $fieldClass }}" :value="old('name', $product->name)" aria-describedby="name-error" />
                        <x-input-error id="name-error" class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descrição (opcional)" />
                        <textarea id="description" name="description" rows="3" class="{{ $selectClass }}">{{ old('description', $product->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-brand-700 focus:ring-brand-500" @checked(old('is_active', $product->is_active))>
                        <x-input-label for="is_active" value="Produto ativo (aparece no cardápio e nos pedidos)" />
                    </div>
                </section>

                <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4" aria-labelledby="dados-variacoes">
                    <div class="flex items-center justify-between gap-3">
                        <h3 id="dados-variacoes" class="text-lg font-semibold text-gray-900">Porções e preços</h3>
                        <button type="button" id="add-variation" class="text-sm font-semibold text-brand-700 hover:text-brand-800 hover:underline focus:outline-none focus:ring-2 focus:ring-gold-500 rounded">
                            Adicionar variação
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('variations')" />

                    <div id="variation-rows" class="space-y-3" data-next-index="{{ count($variationRows) }}">
                        @foreach ($variationRows as $index => $row)
                            @include('menu.partials.variation-row', ['index' => $index, 'row' => $row])
                        @endforeach
                    </div>

                    <template id="variation-row-template">
                        @include('menu.partials.variation-row', ['index' => '__INDEX__', 'row' => ['id' => null, 'name' => '', 'price' => '', 'is_active' => true]])
                    </template>
                </section>

                <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4" aria-labelledby="dados-fiscais">
                    <div>
                        <h3 id="dados-fiscais" class="text-lg font-semibold text-gray-900">Dados fiscais</h3>
                        <p class="mt-1 text-sm text-gray-600">Escolha o tipo do produto: os códigos são preenchidos sozinhos.</p>
                    </div>

                    <div>
                        <x-input-label for="fiscal_profile" value="Tipo do produto" />
                        <select id="fiscal_profile" class="{{ $selectClass }}" data-profiles='@json($fiscalProfiles)'>
                            <option value="">Personalizado</option>
                            @foreach ($fiscalProfiles as $key => $profile)
                                <option value="{{ $key }}" @selected($currentProfile === $key)>{{ $profileLabels[$key] ?? $key }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach (['ncm' => 'NCM', 'cfop' => 'CFOP', 'csosn' => 'CSOSN', 'cest' => 'CEST', 'unit' => 'Unidade'] as $field => $label)
                            <div>
                                <x-input-label :for="$field" :value="$label" />
                                <x-text-input :id="$field" :name="$field" type="text" class="{{ $fieldClass }}" :value="$currentCodes[$field]" aria-describedby="{{ $field }}-error" />
                                <x-input-error id="{{ $field }}-error" class="mt-2" :messages="$errors->get($field)" />
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="flex items-center gap-4">
                    <x-primary-button>Salvar</x-primary-button>
                    <a href="{{ route('menu.index') }}" class="text-sm font-medium text-gray-700 hover:underline">Cancelar</a>
                </div>
            </form>

            @if ($product->exists)
                <section class="bg-white shadow-sm sm:rounded-lg p-6" aria-labelledby="excluir-produto">
                    <h3 id="excluir-produto" class="text-lg font-semibold text-gray-900">Excluir produto</h3>
                    <p class="mt-1 text-sm text-gray-600">Só é possível apagar um produto que nunca foi vendido.</p>
                    <x-danger-button class="mt-4" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-product-deletion')">
                        Excluir produto
                    </x-danger-button>

                    <x-modal name="confirm-product-deletion" focusable>
                        <form method="POST" action="{{ route('menu.products.destroy', $product) }}" class="p-6">
                            @csrf
                            @method('DELETE')
                            <h4 class="text-lg font-medium text-gray-900">Excluir "{{ $product->name }}"?</h4>
                            <p class="mt-1 text-sm text-gray-600">Esta ação não pode ser desfeita.</p>
                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                <x-danger-button>Excluir</x-danger-button>
                            </div>
                        </form>
                    </x-modal>
                </section>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const rows = document.getElementById('variation-rows');
            const template = document.getElementById('variation-row-template');
            let nextIndex = Number(rows.dataset.nextIndex);

            document.getElementById('add-variation').addEventListener('click', function () {
                const html = template.innerHTML.replaceAll('__INDEX__', nextIndex++);
                rows.insertAdjacentHTML('beforeend', html);
                rows.lastElementChild.querySelector('input[type="text"]').focus();
            });

            rows.addEventListener('click', function (event) {
                const button = event.target.closest('[data-remove-variation]');
                if (! button) {
                    return;
                }
                // Toda variação precisa de ao menos uma linha: o preço mora nela.
                if (rows.children.length > 1) {
                    button.closest('[data-variation-row]').remove();
                    document.getElementById('add-variation').focus();
                }
            });

            const profile = document.getElementById('fiscal_profile');
            const profiles = JSON.parse(profile.dataset.profiles);
            const fields = ['ncm', 'cfop', 'csosn', 'cest', 'unit'];

            profile.addEventListener('change', function () {
                const selected = profiles[profile.value];
                if (selected) {
                    fields.forEach((field) => {
                        document.getElementById(field).value = selected[field] ?? '';
                    });
                }
            });

            fields.forEach((field) => {
                document.getElementById(field).addEventListener('input', function () {
                    profile.value = '';
                });
            });
        })();
    </script>
</x-app-layout>
