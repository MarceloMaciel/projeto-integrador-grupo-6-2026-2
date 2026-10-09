<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white uppercase tracking-wide leading-tight flex items-center gap-2">
                    <span>🥢</span>
                    <span>Cardápio do Restaurante</span>
                </h2>
                <p class="text-sm text-brand-100 mt-1">
                    Consulta de pratos, porções, códigos e valores para operação e pedidos.
                </p>
            </div>

            <!-- Summary KPI Badges -->
            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 px-3.5 py-2 rounded-xl shadow-sm text-center">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Categorias</span>
                    <span class="text-lg font-bold text-gray-900">{{ $totalCategoriesCount }}</span>
                </div>
                <div class="bg-white border border-gray-200 px-3.5 py-2 rounded-xl shadow-sm text-center">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Pratos/Itens</span>
                    <span class="text-lg font-bold text-brand-700">{{ $totalProductsCount }}</span>
                </div>
                <div class="bg-white border border-gray-200 px-3.5 py-2 rounded-xl shadow-sm text-center">
                    <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Porções/Preços</span>
                    <span class="text-lg font-bold text-gray-900">{{ $totalVariationsCount }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Search and Filter Controls -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200/80">
                <form method="GET" action="{{ route('menu.index') }}" class="space-y-4">
                    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="busca"
                                value="{{ $searchQuery }}"
                                placeholder="Buscar por código (ex: 99, 38, OV), nome do prato ou ingrediente..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:border-brand-600 focus:ring-brand-600 text-sm shadow-sm transition placeholder-gray-400"
                            />
                            @if(!empty($searchQuery))
                                <a href="{{ route('menu.index', ['categoria' => $selectedCategorySlug]) }}" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600" title="Limpar busca">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <!-- Maintain Category Selection when Submitting Search -->
                        @if(!empty($selectedCategorySlug))
                            <input type="hidden" name="categoria" value="{{ $selectedCategorySlug }}">
                        @endif

                        <!-- Submit Button -->
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold rounded-xl shadow-sm transition duration-150 ease-in-out gap-2 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Buscar
                        </button>

                        @if(!empty($searchQuery) || !empty($selectedCategorySlug))
                            <a href="{{ route('menu.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition duration-150 gap-1.5 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Limpar filtros
                            </a>
                        @endif
                    </div>

                    <!-- Category Pills Horizontal Scroll -->
                    <div class="pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider shrink-0 mr-1">Categorias:</span>

                            <!-- All Categories Pill -->
                            <a
                                href="{{ route('menu.index', ['busca' => $searchQuery]) }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition shrink-0 {{ empty($selectedCategorySlug) ? 'bg-brand-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                            >
                                Todas
                                <span class="text-[10px] {{ empty($selectedCategorySlug) ? 'bg-brand-800 text-white' : 'bg-gray-200 text-gray-600' }} px-1.5 py-0.5 rounded-full">
                                    {{ $totalProductsCount }}
                                </span>
                            </a>

                            @foreach($categories as $category)
                                @php
                                    $isSelected = $selectedCategorySlug === $category->slug;
                                @endphp
                                <a
                                    href="{{ route('menu.index', ['categoria' => $category->slug, 'busca' => $searchQuery]) }}"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition shrink-0 {{ $isSelected ? 'bg-brand-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                >
                                    {{ $category->name }}
                                    <span class="text-[10px] {{ $isSelected ? 'bg-brand-800 text-white' : 'bg-gray-200 text-gray-600' }} px-1.5 py-0.5 rounded-full">
                                        {{ $category->all_products_count ?? $category->products_count }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            <!-- Active Filter Banner (when filter or search active) -->
            @if(!empty($searchQuery) || !empty($selectedCategorySlug))
                <div class="flex items-center justify-between bg-brand-50/70 border border-brand-200/80 px-4 py-3 rounded-xl text-sm">
                    <div class="flex items-center gap-2 flex-wrap text-brand-900">
                        <span class="font-medium">Filtrando por:</span>
                        @if(!empty($selectedCategorySlug))
                            @php
                                $currentCat = $categories->firstWhere('slug', $selectedCategorySlug);
                            @endphp
                            <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-800 text-xs font-semibold px-2.5 py-1 rounded-lg border border-brand-200">
                                Categoria: {{ $currentCat?->name ?? $selectedCategorySlug }}
                                <a href="{{ route('menu.index', ['busca' => $searchQuery]) }}" class="hover:text-brand-950 font-bold">&times;</a>
                            </span>
                        @endif
                        @if(!empty($searchQuery))
                            <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-800 text-xs font-semibold px-2.5 py-1 rounded-lg border border-brand-200">
                                Termo: "{{ $searchQuery }}"
                                <a href="{{ route('menu.index', ['categoria' => $selectedCategorySlug]) }}" class="hover:text-brand-950 font-bold">&times;</a>
                            </span>
                        @endif
                        <span class="text-xs text-brand-800">({{ $products->count() }} {{ $products->count() === 1 ? 'item encontrado' : 'itens encontrados' }})</span>
                    </div>

                    <a href="{{ route('menu.index') }}" class="text-xs font-semibold text-brand-800 hover:text-brand-950 underline shrink-0">
                        Ver cardápio completo
                    </a>
                </div>
            @endif

            <!-- Main Content: Grouped by Category vs Filtered List -->
            @if($groupedByCategory)
                <!-- Quick Category Jump Navigation (when viewing all categories) -->
                <div class="bg-white/90 backdrop-blur border border-gray-200 rounded-xl p-3 shadow-xs sticky top-2 z-10 hidden sm:flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider shrink-0 mr-1">Ir para:</span>
                    @foreach($categoriesWithProducts as $category)
                        <a href="#cat-{{ $category->slug }}" class="text-xs font-medium text-gray-600 hover:text-brand-700 hover:bg-brand-50 px-2.5 py-1 rounded-lg transition shrink-0">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>

                <div class="space-y-10">
                    @foreach($categoriesWithProducts as $category)
                        @php
                            $categoryProducts = $category->allProducts;
                        @endphp
                        @if($categoryProducts->isNotEmpty())
                            <section id="cat-{{ $category->slug }}" class="scroll-mt-20 space-y-4">
                                <!-- Category Section Header -->
                                <x-framed-title>{{ $category->name }}</x-framed-title>

                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold text-gray-800">{{ $categoryProducts->count() }} {{ $categoryProducts->count() === 1 ? 'item' : 'itens' }}</span>
                                        @if(!empty($category->description))
                                            &middot; {{ $category->description }}
                                        @endif
                                    </p>
                                    <a href="{{ route('menu.index', ['categoria' => $category->slug]) }}" class="shrink-0 text-xs font-medium text-brand-700 hover:text-brand-800 hover:underline">
                                        Filtrar categoria &rarr;
                                    </a>
                                </div>

                                <!-- Category Product Cards Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach($categoryProducts as $product)
                                        @include('menu.partials.product-card', ['product' => $product])
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endforeach
                </div>

            @else
                <!-- Filtered / Search Results Grid -->
                @if($products->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center border border-gray-200 shadow-sm space-y-4 max-w-md mx-auto">
                        <div class="w-16 h-16 bg-brand-50 text-brand-700 rounded-full flex items-center justify-center mx-auto text-2xl">
                            🔍
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Nenhum item encontrado</h3>
                        <p class="text-sm text-gray-500">
                            Não encontramos nenhum prato correspondente aos filtros selecionados. Tente buscar por outro código, nome ou ingrediente.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('menu.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                                Ver todo o cardápio
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($products as $product)
                            @include('menu.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
