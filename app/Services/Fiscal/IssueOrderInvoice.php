<?php

namespace App\Services\Fiscal;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class IssueOrderInvoice
{
    public function __construct(private readonly NfceXmlBuilder $builder) {}

    public function issue(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Um duplo clique devolve o documento já gravado, sem gerar outro XML.
            if ($existing = $order->invoice()->first()) {
                return $existing;
            }

            $order->load('items');
            if ($order->status !== Order::STATUS_CLOSED || $order->items->isEmpty()) {
                throw ValidationException::withMessages(['invoice' => 'A nota exige um pedido fechado com itens.']);
            }

            $totalCents = (int) round((float) $order->total * 100);
            $itemsCents = $order->items->sum(fn ($item) => (int) round($item->quantity * (float) $item->unit_price * 100));
            $paidCents = (int) round(((float) $order->amount_received - (float) $order->change) * 100);
            if ($totalCents <= 0 || $itemsCents !== $totalCents || $paidCents !== $totalCents) {
                throw ValidationException::withMessages(['invoice' => 'Os itens, o total e o pagamento do pedido precisam conferir antes de gerar a nota.']);
            }

            $items = $order->items->map(fn ($item) => [
                ...$item->only(['code', 'description', 'ncm', 'cfop', 'csosn', 'cest', 'unit', 'quantity']),
                'unit_price' => (float) $item->unit_price,
            ])->all();

            $snapshot = [
                'emitter' => config('fiscal.emitter'),
                'consumer_cpf' => $order->consumer_cpf,
                'items' => $items,
                'total' => $order->total,
                'payment_method' => $order->payment_method_name,
                'payments' => [['method' => $order->payment_method, 'amount' => (float) $order->amount_received]],
                'change' => (float) $order->change,
            ];

            $invoice = $order->invoice()->create([
                'series' => config('fiscal.series'),
                'issued_at' => now(),
                'snapshot' => $snapshot,
            ]);

            $xml = $this->builder->build([
                ...$snapshot,
                'number' => $invoice->id,
                'issued_at' => $invoice->issued_at,
            ]);
            $document = new SimpleXMLElement($xml);
            $invoice->update([
                'xml' => $xml,
                'access_key' => substr((string) $document->infNFe['Id'], 3),
            ]);

            return $invoice;
        }, 3);
    }
}
