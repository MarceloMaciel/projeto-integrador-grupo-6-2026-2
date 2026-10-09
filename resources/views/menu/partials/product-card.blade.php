<div class="bg-white rounded-2xl border border-gray-200/90 hover:border-brand-300 hover:shadow-md transition-all duration-200 p-5 flex flex-col justify-between group">
    <div class="space-y-3">
        <!-- Top row: Code badge & Categories -->
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-1.5 flex-wrap">
                @if(!empty($product->code))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-gold-400 text-brand-950 font-mono">
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
            <span class="text-xs font-bold text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-lg">
                {{ $product->formatted_price_range }}
            </span>
        </div>

        <!-- Product Name -->
        <div>
            <h4 class="text-base font-bold text-gray-900 group-hover:text-brand-800 transition">
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
                <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-gray-50/80 hover:bg-brand-50/50 border border-gray-100 transition text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-800">{{ $variation->name }}</span>
                        @if(!empty($variation->description) && $variation->description !== $variation->name)
                            <span class="text-gray-400 text-[11px]">({{ $variation->description }})</span>
                        @endif
                    </div>
                    <span class="font-bold text-brand-800">
                        {{ $variation->formatted_price }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
