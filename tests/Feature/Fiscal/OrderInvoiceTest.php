<?php

namespace Tests\Feature\Fiscal;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Fiscal\NfceXmlBuilder;
use App\Services\Fiscal\TestCertificate;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SimpleXMLElement;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private string $certificatePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
        $this->certificatePath = sys_get_temp_dir().'/order-nfce-'.uniqid().'.pfx';
        config([
            'fiscal.certificate.path' => $this->certificatePath,
            'fiscal.environment' => 2,
            'fiscal.series' => 1,
            'fiscal.emitter.cnpj' => '11222333000181',
            'fiscal.emitter.state_registration' => '123456789012',
            'fiscal.emitter.legal_name' => 'EMPRESA DE TESTE LTDA',
            'fiscal.emitter.trade_name' => 'EMPRESA DE TESTE',
            'fiscal.emitter.address.state' => 'SP',
        ]);
        $this->app->make(TestCertificate::class)->generate('EMPRESA DE TESTE LTDA', '11222333000181');
    }

    protected function tearDown(): void
    {
        @unlink($this->certificatePath);
        parent::tearDown();
    }

    private function order(string $payment = '01', ?string $cpf = '52998224725'): Order
    {
        $variation = Product::byCode('38')->firstOrFail()->variations->firstWhere('name', 'Grande');
        $this->actingAs(User::factory()->create())->post(route('orders.store'), [
            'items' => [['variation_id' => $variation->id, 'quantity' => 2]],
            'payment_method' => $payment,
            'amount_received' => '120.00',
            'consumer_cpf' => $cpf,
        ])->assertSessionHasNoErrors();

        return Order::latest('id')->firstOrFail();
    }

    public function test_generates_signed_xml_from_the_sale_and_keeps_it_on_repeat(): void
    {
        $order = $this->order();
        Product::byCode('38')->firstOrFail()->variations()->update(['price' => 999]);

        $this->post(route('orders.invoice.store', $order))->assertRedirect(route('orders.show', $order));
        $invoice = $order->invoice()->firstOrFail();
        $xml = new SimpleXMLElement($invoice->xml);

        $this->assertSame('100.00', (string) $xml->infNFe->total->ICMSTot->vNF);
        $this->assertSame('120.00', (string) $xml->infNFe->pag->detPag->vPag);
        $this->assertSame('20.00', (string) $xml->infNFe->pag->vTroco);
        $this->assertSame('52998224725', (string) $xml->infNFe->dest->CPF);
        $this->assertSame('2', (string) $xml->infNFe->ide->tpAmb);
        $this->assertNotEmpty((string) $xml->Signature->SignatureValue);
        $this->assertSame(44, strlen($invoice->access_key));

        $this->post(route('orders.invoice.store', $order))->assertSessionHasNoErrors();
        $this->assertSame(1, Invoice::count());
        $this->assertSame($invoice->xml, $invoice->fresh()->xml);
        $this->get(route('orders.invoice.xml', $order))
            ->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertContent($invoice->xml);
    }

    public function test_receipt_uses_preserved_data_and_history_links_to_order(): void
    {
        $order = $this->order('17', null);
        $this->post(route('orders.invoice.store', $order));
        config(['fiscal.emitter.legal_name' => 'OUTRO EMITENTE']);
        $order->items()->update(['description' => 'Alterado depois']);

        $this->get(route('orders.invoice.receipt', $order))
            ->assertOk()->assertSee('EMPRESA DE TESTE LTDA')->assertDontSee('OUTRO EMITENTE')
            ->assertSee(NfceXmlBuilder::HOMOLOGATION_ITEM)->assertDontSee('Alterado depois')
            ->assertSee('CONSUMIDOR NÃO IDENTIFICADO')->assertSee('SEM VALOR FISCAL')
            ->assertSee('Não gera créditos na Nota Fiscal Paulista.')->assertSee('window.print()', false);
        $this->get(route('orders.index'))->assertOk()->assertSee(route('orders.show', $order));
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Baixar XML')->assertDontSee('Gerar NFC-e simulada');
    }

    public function test_generation_failure_rolls_back_invoice_and_can_be_retried(): void
    {
        $order = $this->order();
        unlink($this->certificatePath);

        $this->post(route('orders.invoice.store', $order))
            ->assertRedirect(route('orders.show', $order))->assertSessionHas('error');
        $this->assertSame(0, Invoice::count());
        $this->assertModelExists($order);
        $this->assertCount(1, $order->items);

        $this->app->make(TestCertificate::class)->generate('EMPRESA DE TESTE LTDA', '11222333000181');
        $this->post(route('orders.invoice.store', $order))->assertSessionHas('status');
        $this->assertSame(1, Invoice::count());
    }

    public function test_refuses_production_environment_and_empty_orders(): void
    {
        $order = $this->order();
        config(['fiscal.environment' => 1]);
        $this->post(route('orders.invoice.store', $order))->assertSessionHas('error');
        $this->assertSame(0, Invoice::count());

        config(['fiscal.environment' => 2]);
        $order->items()->delete();
        $this->post(route('orders.invoice.store', $order))->assertSessionHasErrors('invoice');
        $this->assertSame(0, Invoice::count());
    }

    public function test_missing_invoice_returns_404_and_guests_cannot_access_it(): void
    {
        $order = $this->order();
        $this->get(route('orders.invoice.xml', $order))->assertNotFound();
        $this->get(route('orders.invoice.receipt', $order))->assertNotFound();

        auth()->logout();
        $this->post(route('orders.invoice.store', $order))->assertRedirect(route('login'));
        $this->get(route('orders.invoice.xml', $order))->assertRedirect(route('login'));
        $this->get(route('orders.invoice.receipt', $order))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_refuses_inconsistent_totals_without_saving_a_document(): void
    {
        $order = $this->order();
        $order->update(['total' => '1.00']);

        $this->post(route('orders.invoice.store', $order))->assertSessionHasErrors('invoice');
        $this->assertSame(0, Invoice::count());
    }

    public function test_danfe_prints_xml_fields_and_counts_product_lines_instead_of_units(): void
    {
        $order = $this->order();
        $drink = Product::where('name', 'Cerveja Lata 350ml')->firstOrFail()->variations->first();
        $item = OrderItem::fromVariation($drink, 3);
        $order->items()->save($item);
        $order->update([
            'total' => 100 + (float) $item->total,
            'amount_received' => 120 + (float) $item->total,
        ]);
        $this->post(route('orders.invoice.store', $order))->assertSessionHasNoErrors();
        $invoice = $order->invoice()->firstOrFail();
        // O DANFE deve seguir o XML mesmo que o snapshot auxiliar seja alterado.
        $invoice->update(['snapshot' => ['emitter' => ['legal_name' => 'SNAPSHOT ALTERADO']]]);

        $this->get(route('orders.invoice.receipt', $order))
            ->assertOk()
            ->assertSee('Documento Auxiliar da Nota Fiscal de Consumidor Eletrônica')
            ->assertSee('11.222.333/0001-81')->assertSee('CONSUMIDOR CPF: 529.982.247-25')
            ->assertSee(NfceXmlBuilder::HOMOLOGATION_ITEM)->assertSee('Cerveja Lata 350ml')
            ->assertSee('<div class="total-row"><span>Qtde. total de itens</span><span>2</span></div>', false)
            ->assertSee(implode(' ', str_split($invoice->access_key, 4)))
            ->assertSee('src="data:image/svg+xml;base64,', false)
            ->assertSee('Protocolo de autorização: inexistente')
            ->assertSee('EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO – SEM VALOR FISCAL')
            ->assertDontSee('SNAPSHOT ALTERADO')
            ->assertDontSee('Acréscimos R$')->assertDontSee('Desconto R$');
    }
}
