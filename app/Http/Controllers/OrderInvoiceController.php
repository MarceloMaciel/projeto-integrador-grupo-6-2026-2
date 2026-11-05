<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Fiscal\IssueOrderInvoice;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use Throwable;

class OrderInvoiceController extends Controller
{
    public function store(Order $order, IssueOrderInvoice $issuer): RedirectResponse
    {
        try {
            $issuer->issue($order);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('orders.show', $order)->with('error',
                'Não foi possível gerar a NFC-e simulada. Verifique o certificado de teste e os dados fiscais. O pedido foi preservado; tente novamente após a correção.');
        }

        return redirect()->route('orders.show', $order)->with('status', 'NFC-e simulada disponível. Sem valor fiscal.');
    }

    public function download(Order $order): Response
    {
        $invoice = $order->invoice()->firstOrFail();

        return response($invoice->xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="nfce-simulada-'.$invoice->id.'.xml"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function receipt(Order $order): Response
    {
        $invoice = $order->invoice()->firstOrFail();
        $document = new SimpleXMLElement($invoice->xml);
        $qrContent = (string) $document->infNFeSupl->qrCode;
        $writer = new Writer(new ImageRenderer(new RendererStyle(256, 0), new SvgImageBackEnd));

        return response()->view('orders.receipt', [
            'invoice' => $invoice,
            'nfce' => $document->infNFe,
            'consultationUrl' => (string) $document->infNFeSupl->urlChave,
            'qrCodeImage' => $qrContent !== ''
                ? 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($qrContent, 'UTF-8', ErrorCorrectionLevel::M()))
                : null,
        ])->header('Cache-Control', 'private, no-store');
    }
}
