@php($money = fn ($value) => 'R$ '.number_format((float) $value, 2, ',', '.'))

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white uppercase tracking-wide leading-tight">
            Pedido nº {{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div role="status" class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">{{ session('error') }}</div>
            @endif
            <x-input-error :messages="$errors->get('invoice')" />

            <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3" aria-labelledby="nota-pedido">
                <h3 id="nota-pedido" class="text-lg font-semibold text-gray-900">NFC-e simulada</h3>
                <p class="text-sm text-gray-600">Sem valor fiscal. Este documento não é transmitido à SEFAZ e não gera créditos na Nota Fiscal Paulista.</p>
                @if ($order->invoice)
                    <p class="text-sm text-gray-900">Número {{ $order->invoice->id }} · Série {{ $order->invoice->series }} · Gerada em {{ $order->invoice->issued_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('orders.invoice.xml', $order) }}" class="text-sm font-semibold text-brand-700 hover:underline">Baixar XML</a>
                        <a href="{{ route('orders.invoice.receipt', $order) }}" class="text-sm font-semibold text-brand-700 hover:underline">Imprimir / salvar PDF</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('orders.invoice.store', $order) }}">
                        @csrf
                        <x-primary-button>Gerar NFC-e simulada</x-primary-button>
                    </form>
                @endif
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4" aria-labelledby="itens-pedido">
                <h3 id="itens-pedido" class="text-lg font-semibold text-gray-900">Itens</h3>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 border-b border-gray-200">
                            <th scope="col" class="py-2 font-semibold">Item</th>
                            <th scope="col" class="py-2 font-semibold text-right">Qtd.</th>
                            <th scope="col" class="py-2 font-semibold text-right">Unitário</th>
                            <th scope="col" class="py-2 font-semibold text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-2 text-gray-900">{{ $item->description }}</td>
                                <td class="py-2 text-right">{{ $item->quantity }}</td>
                                <td class="py-2 text-right">{{ $money($item->unit_price) }}</td>
                                <td class="py-2 text-right">{{ $money($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 font-bold text-gray-900">
                            <th scope="row" colspan="3" class="py-2 text-left">Total</th>
                            <td class="py-2 text-right">{{ $money($order->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <section class="bg-white shadow-sm sm:rounded-lg p-6" aria-labelledby="pagamento-pedido">
                <h3 id="pagamento-pedido" class="text-lg font-semibold text-gray-900">Pagamento</h3>

                <dl class="mt-3 grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-gray-600">Forma de pagamento</dt>
                    <dd class="text-right text-gray-900">{{ $order->payment_method_name }}</dd>
                    <dt class="text-gray-600">Valor recebido</dt>
                    <dd class="text-right text-gray-900">{{ $money($order->amount_received) }}</dd>
                    <dt class="text-gray-600">Troco</dt>
                    <dd class="text-right text-gray-900">{{ $money($order->change) }}</dd>
                    <dt class="text-gray-600">CPF na nota</dt>
                    <dd class="text-right text-gray-900">{{ $order->consumer_cpf ?? 'Não informado' }}</dd>
                    <dt class="text-gray-600">Fechado em</dt>
                    <dd class="text-right text-gray-900">{{ $order->closed_at->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</dd>
                </dl>
            </section>

            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                Novo pedido
            </a>
            <a href="{{ route('orders.index') }}" class="inline-flex ms-4 text-sm font-semibold text-brand-700 hover:underline">Histórico de pedidos</a>
        </div>
    </div>
</x-app-layout>
