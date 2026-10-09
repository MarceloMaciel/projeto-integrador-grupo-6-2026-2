@props(['as' => 'h3'])

{{-- Título com a moldura dourada dupla das seções do cardápio impresso. --}}
<div {{ $attributes->merge(['class' => 'rounded-md bg-brand-700 p-1']) }}>
    <{{ $as }} class="rounded border border-gold-500 px-4 py-2 text-center text-lg font-bold uppercase tracking-wide text-white ring-1 ring-inset ring-gold-500/60">
        {{ $slot }}
    </{{ $as }}>
</div>
