@php
    $emitter = $nfce->emit;
    $address = $emitter->enderEmit;
    $totals = $nfce->total->ICMSTot;
    $money = fn ($value) => number_format((float) $value, 2, ',', '.');
    $quantity = fn ($value) => rtrim(rtrim(number_format((float) $value, 4, ',', '.'), '0'), ',');
    $cnpj = preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', (string) $emitter->CNPJ);
    $cpf = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', (string) $nfce->dest->CPF);
    $accessKey = substr((string) $nfce['Id'], 3);
    $additions = (float) $totals->vFrete + (float) $totals->vSeg + (float) $totals->vOutro;
    $discount = (float) $totals->vDesc;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>DANFE NFC-e simulado {{ $nfce->ide->nNF }} — Sem valor fiscal</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 24px auto; padding: 0 12px; background: #f3f4f6; color: #000; font-family: Arial, sans-serif; }
        .actions { max-width: 620px; margin: 0 auto 20px; display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; font-size: 14px; }
        .actions button, .actions a { font: inherit; color: #111; padding: 8px 12px; background: #fff; border: 1px solid #aaa; border-radius: 4px; cursor: pointer; }
        .print-help { max-width: 620px; margin: 0 auto 16px; font-size: 13px; text-align: center; }
        .receipt { width: 80mm; max-width: 100%; margin: auto; padding: 2mm; background: white; font-size: 9pt; line-height: 1.3; overflow-wrap: anywhere; }
        .block { padding: 2mm 0; border-bottom: 1px dashed #000; }
        .center { text-align: center; }
        h1 { font-size: 10pt; margin: 1mm 0; } p { margin: 1mm 0; }
        .subtitle { font-size: 9pt; font-weight: normal; }
        .notice { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8pt; }
        th, td { padding: 1mm 0; vertical-align: top; text-align: left; }
        th { font-weight: bold; }
        .code { width: 15%; padding-right: 1mm; } .description { width: 59%; padding-right: 1mm; } .amount { width: 26%; text-align: right; white-space: nowrap; }
        .item-detail { font-size: 8pt; font-weight: normal; }
        .total-row { display: flex; justify-content: space-between; gap: 2mm; margin: 1mm 0; }
        .total-row span:first-child { min-width: 0; } .total-row span:last-child { white-space: nowrap; }
        .key, .url { font-size: 8pt; overflow-wrap: anywhere; }
        .qr-zone { width: 40mm; padding: 4mm; margin: 1mm auto; background: #fff; }
        .qr-zone img { display: block; width: 32mm; height: 32mm; }
        .no-break { break-inside: avoid; }
        .footnote { padding: 2mm 0; font-size: 8pt; }
        @page { margin: 0; }
        @media print {
            body { margin: 0; padding: 0; background: #fff; }
            .actions, .print-help { display: none; }
            .receipt { margin: 0 auto; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <nav class="actions" aria-label="Ações do comprovante">
        <button type="button" onclick="window.print()">Imprimir / salvar PDF</button>
        <a href="{{ route('orders.invoice.xml', $invoice->order_id) }}">Baixar XML</a>
        <a href="{{ route('orders.show', $invoice->order_id) }}">Voltar ao pedido</a>
    </nav>
    <p class="print-help">Cupom de 80 mm. Na impressão, use escala de 100% e desative os cabeçalhos e rodapés do navegador. Também pode ser salvo em PDF ou impresso em A4.</p>
    <main class="receipt" aria-label="DANFE NFC-e simulado">
        <header class="block center">
            <p>CNPJ: {{ $cnpj }}</p>
            <h1>{{ $emitter->xNome }}</h1>
            <p>{{ $address->xLgr }}, {{ $address->nro }}{{ (string) $address->xCpl !== '' ? ' — '.$address->xCpl : '' }}<br>
                {{ $address->xBairro }} — {{ $address->xMun }}/{{ $address->UF }}<br>
                CEP {{ $address->CEP }}{{ (string) $address->fone !== '' ? ' · Fone: '.$address->fone : '' }}</p>
            <p class="subtitle">Documento Auxiliar da Nota Fiscal de Consumidor Eletrônica</p>
            <p class="notice">DANFE NFC-e SIMULADO — SEM VALOR FISCAL</p>
        </header>

        <section class="block" aria-label="Produtos e serviços">
            <table>
                <thead><tr><th scope="col" class="code">Código</th><th scope="col" class="description">Descrição</th><th scope="col" class="amount">Vl. total</th></tr></thead>
                <tbody>
                    @foreach ($nfce->det as $detail)
                        @php($item = $detail->prod)
                        <tr>
                            <td class="code">{{ $item->cProd }}</td>
                            <td class="description">{{ $item->xProd }}<br><span class="item-detail">Qtde.: {{ $quantity($item->qCom) }} · Un.: {{ $item->uCom }}<br>Vl. unit.: {{ $money($item->vUnCom) }}</span></td>
                            <td class="amount">{{ $money($item->vProd) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="block" aria-label="Totais e pagamento">
            <div class="total-row"><span>Qtde. total de itens</span><span>{{ count($nfce->det) }}</span></div>
            <div class="total-row"><span>Valor total R$</span><span>{{ $money($totals->vProd) }}</span></div>
            @if ($additions > 0)
                <div class="total-row"><span>Acréscimos R$</span><span>{{ $money($additions) }}</span></div>
            @endif
            @if ($discount > 0)
                <div class="total-row"><span>Desconto R$</span><span>{{ $money($discount) }}</span></div>
            @endif
            @if ($additions > 0 || $discount > 0)
                <div class="total-row"><strong>Valor a pagar R$</strong><strong>{{ $money($totals->vNF) }}</strong></div>
            @endif
            <div class="total-row"><strong>FORMA DE PAGAMENTO</strong><strong>VALOR PAGO R$</strong></div>
            @foreach ($nfce->pag->detPag as $payment)
                <div class="total-row"><span>{{ config('fiscal.payment_methods.'.(string) $payment->tPag, (string) $payment->tPag) }}</span><span>{{ $money($payment->vPag) }}</span></div>
            @endforeach
            <div class="total-row"><span>Troco R$</span><span>{{ $money($nfce->pag->vTroco) }}</span></div>
        </section>

        <section class="block center" aria-label="Consulta e identificação da NFC-e">
            <p><strong>Consulte pela Chave de Acesso em</strong></p>
            <p class="url">{{ $consultationUrl }}</p>
            <p class="key">{{ implode(' ', str_split($accessKey, 4)) }}</p>
            <p class="notice">Consulta ilustrativa: nota não registrada na SEFAZ.</p>
            <div class="no-break">
                <p><strong>{{ $cpf !== '' ? 'CONSUMIDOR CPF: '.$cpf : 'CONSUMIDOR NÃO IDENTIFICADO' }}</strong></p>
                <p>NFC-e nº {{ $nfce->ide->nNF }} · Série {{ $nfce->ide->serie }}<br>
                    {{ \Illuminate\Support\Carbon::parse((string) $nfce->ide->dhEmi)->timezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}</p>
                <p>Protocolo de autorização: inexistente — simulação local.</p>
                @if ($qrCodeImage)
                    <div class="qr-zone"><img src="{{ $qrCodeImage }}" alt="QR Code do XML simulado, sem registro na SEFAZ"></div>
                @endif
            </div>
        </section>

        <section class="block center notice" aria-label="Mensagem fiscal">
            <p>EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO – SEM VALOR FISCAL</p>
            @if ((string) $nfce->infAdic->infAdFisco !== '')
                <p>{{ $nfce->infAdic->infAdFisco }}</p>
            @endif
        </section>
        @if ((string) $nfce->infAdic->infCpl !== '')
            <section class="block center" aria-label="Informações complementares"><p>{{ $nfce->infAdic->infCpl }}</p></section>
        @endif
        <footer class="footnote center">
            <p>Não transmitida nem autorizada pela SEFAZ. Não gera créditos na Nota Fiscal Paulista.</p>
            <p>Demonstração acadêmica · Pedido {{ $invoice->order_id }}.</p>
        </footer>
    </main>
</body>
</html>
