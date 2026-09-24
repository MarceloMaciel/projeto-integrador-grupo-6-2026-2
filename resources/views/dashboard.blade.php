<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <section class="bg-white shadow-sm sm:rounded-lg p-6" aria-labelledby="boas-vindas">
                <h3 id="boas-vindas" class="text-lg font-semibold text-gray-900">
                    Olá, {{ Auth::user()->name }}
                </h3>
                <p class="mt-1 text-gray-600">
                    Este é o sistema de geração de notas fiscais do
                    <strong>{{ config('app.name') }}</strong>.
                    Use os módulos abaixo para consultar o cardápio, lançar os pedidos
                    e emitir as notas fiscais.
                </p>
            </section>

            <section aria-labelledby="modulos">
                <h3 id="modulos" class="text-base font-semibold text-gray-900 mb-3">
                    Módulos do Sistema
                </h3>

                <ul role="list" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Módulo Cardápio (Ativo) -->
                    <li class="bg-white shadow-sm sm:rounded-lg p-5 border border-amber-200 hover:border-amber-400 hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 text-base">Cardápio</h4>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-1">
                                    Disponível
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                Consulta rápida dos produtos, porções, códigos e tabelas de preços orientais.
                            </p>
                        </div>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500 font-medium">10 categorias cadastradas</span>
                            <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 hover:text-amber-700 hover:underline">
                                Acessar cardápio
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </li>

                    <!-- Módulo Novo Pedido -->
                    <li class="bg-white shadow-sm sm:rounded-lg p-5 border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-gray-50 text-gray-500 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 text-base">Novo pedido</h4>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2 py-1">
                                    Em desenvolvimento
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                Montagem da comanda com os itens consumidos, porções e cálculo automático do total.
                            </p>
                        </div>
                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Em breve</span>
                        </div>
                    </li>

                    <!-- Módulo Notas Emitidas -->
                    <li class="bg-white shadow-sm sm:rounded-lg p-5 border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-gray-50 text-gray-500 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 text-base">Notas emitidas</h4>
                                </div>
                                <span class="shrink-0 text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2 py-1">
                                    Em desenvolvimento
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                Histórico de pedidos atendidos e emissão de notas fiscais em conformidade.
                            </p>
                        </div>
                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Em breve</span>
                        </div>
                    </li>
                </ul>
            </section>

        </div>
    </div>
</x-app-layout>
