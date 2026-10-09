@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-gold-400 text-sm font-semibold leading-5 text-gold-400 focus:outline-none focus:border-white transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-brand-100 hover:text-white hover:border-gold-500 focus:outline-none focus:text-white focus:border-gold-500 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
