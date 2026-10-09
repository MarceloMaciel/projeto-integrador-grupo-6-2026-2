<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-800 focus:bg-brand-800 active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
