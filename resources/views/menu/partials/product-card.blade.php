<div class="bg-white rounded-2xl border border-gray-200/90 hover:border-amber-300 hover:shadow-md transition-all duration-200 p-5 flex flex-col justify-between group">
    <div class="space-y-3">
        <!-- Top row: Code badge & Categories -->
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-1.5 flex-wrap">
                @if(!empty($product->code))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-amber-100 text-amber-900 border border-amber-200/80 shadow-2xs font-mono">
                        #{{ $product->code }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
                        Item
                    </span>
                @endif

                @foreach($product->categories as $cat)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                        {{ $cat->name }}
                    </span>
                @endforeach
            </div>

            <!-- Price summary badge -->
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">
                {{ $product->formatted_price_range }}
            </span>
        </div>

        <!-- Product Name -->
        <div>
            <h4 class="text-base font-bold text-gray-900 group-hover:text-amber-700 transition">
                {{ $product->name }}
            </h4>
            @if(!empty($product->description))
                <p class="text-xs text-gray-600 mt-1.5 leading-relaxed line-clamp-3">
                    {{ $product->description }}
                </p>
            @endif
        </div>
    </div>

    <!-- Variations / Pricing Table -->
    <div class="mt-4 pt-3 border-t border-gray-100">
        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-2">
            {{ $product->variations->count() > 1 ? 'Porções / Opções' : 'Valor' }}
        </span>

        <div class="space-y-1.5">
            @foreach($product->variations as $variation)
                <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-gray-50/80 hover:bg-amber-50/50 border border-gray-100 transition text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-800">{{ $variation->name }}</span>
                        @if(!empty($variation->description) && $variation->description !== $variation->name)
                            <span class="text-gray-400 text-[11px]">({{ $variation->description }})</span>
                        @endif
                    </div>
                    <span class="font-bold text-emerald-700">
                        {{ $variation->formatted_price }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
