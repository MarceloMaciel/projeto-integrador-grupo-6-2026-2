<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white uppercase tracking-wide">Pedidos e notas</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-600">Consulte os pedidos para gerar ou baixar suas NFC-e simuladas. Documentos sem valor fiscal.</p>
            <a href="{{ route('orders.create') }}" class="inline-flex px-4 py-2 bg-brand-700 text-white font-semibold rounded-xl">Novo pedido</a>
            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead><tr class="border-b border-gray-200">
                        <th scope="col" class="p-4">Pedido</th><th scope="col" class="p-4">Fechado em</th>
                        <th scope="col" class="p-4">Total</th><th scope="col" class="p-4">NFC-e simulada</th><th scope="col" class="p-4">Ação</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-b border-gray-100">
                                <td class="p-4">{{ $order->id }}</td>
                                <td class="p-4">{{ $order->closed_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                                <td class="p-4 whitespace-nowrap">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td>
                                <td class="p-4">{{ $order->invoice ? 'Gerada · nº '.$order->invoice->id : 'Pendente' }}</td>
                                <td class="p-4"><a class="font-semibold text-brand-700 hover:underline" href="{{ route('orders.show', $order) }}" aria-label="Abrir pedido {{ $order->id }}">Abrir pedido</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-6 text-gray-600">Nenhum pedido cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
