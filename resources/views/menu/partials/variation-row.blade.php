<div data-variation-row class="grid gap-3 sm:grid-cols-[1fr_9rem_auto_auto] sm:items-start border border-gray-200 rounded-lg p-3">
    @if (! empty($row['id']))
        <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $row['id'] }}">
    @endif

    <div>
        <x-input-label for="variation-{{ $index }}-name" value="Nome da porção" />
        <x-text-input id="variation-{{ $index }}-name" name="variations[{{ $index }}][name]" type="text" required class="mt-1 block w-full" :value="$row['name']" aria-describedby="variation-{{ $index }}-name-error" />
        <x-input-error id="variation-{{ $index }}-name-error" class="mt-2" :messages="$errors->get('variations.'.$index.'.name')" />
    </div>

    <div>
        <x-input-label for="variation-{{ $index }}-price" value="Preço (R$)" />
        <x-text-input id="variation-{{ $index }}-price" name="variations[{{ $index }}][price]" type="number" min="0.01" step="0.01" required class="mt-1 block w-full" :value="$row['price']" aria-describedby="variation-{{ $index }}-price-error" />
        <x-input-error id="variation-{{ $index }}-price-error" class="mt-2" :messages="$errors->get('variations.'.$index.'.price')" />
    </div>

    <div class="flex items-center gap-2 sm:mt-7">
        <input type="hidden" name="variations[{{ $index }}][is_active]" value="0">
        <input id="variation-{{ $index }}-active" name="variations[{{ $index }}][is_active]" type="checkbox" value="1" class="rounded border-gray-300 text-brand-700 focus:ring-brand-500" @checked($row['is_active'] ?? true)>
        <x-input-label for="variation-{{ $index }}-active" value="Ativa" />
    </div>

    <button type="button" data-remove-variation aria-label="Remover variação {{ $row['name'] }}" class="sm:mt-7 text-sm font-medium text-red-700 hover:underline focus:outline-none focus:ring-2 focus:ring-gold-500 rounded">
        Remover
    </button>
</div>
