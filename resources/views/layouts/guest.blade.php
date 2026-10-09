<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-brand-700 to-brand-900">
            <div class="flex flex-col items-center gap-3">
                <a href="/" class="rounded-md focus:outline-none focus:ring-2 focus:ring-gold-400 focus:ring-offset-4 focus:ring-offset-brand-700">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-56 h-auto">
                </a>
                <p class="text-sm font-semibold uppercase tracking-widest text-gold-400">Sistema de notas fiscais</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-lg overflow-hidden border-2 border-gold-500 sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
