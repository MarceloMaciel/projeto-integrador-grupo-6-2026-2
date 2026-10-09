<?php

namespace Tests\Feature\Fiscal;

use App\Services\Fiscal\NfceXmlBuilder;
use App\Services\Fiscal\TestCertificate;
use Illuminate\Support\Carbon;
use SimpleXMLElement;
use Tests\TestCase;
use Throwable;

class NfceXmlBuilderTest extends TestCase
{
    private string $certificatePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->certificatePath = sys_get_temp_dir().'/nfce-test-'.uniqid().'.pfx';
        config(['fiscal.certificate.path' => $this->certificatePath]);

        $this->app->make(TestCertificate::class)->generate('EMPRESA DE TESTE LTDA', '11222333000181');
    }

    protected function tearDown(): void
    {
        @unlink($this->certificatePath);

        parent::tearDown();
    }

    public function test_builds_a_signed_nfce_that_passes_the_official_xsd(): void
    {
        // signNFe() valida contra o XSD e lança exceção se o XML for inválido.
        $nfce = $this->parse($this->build());

        $this->assertSame('65', (string) $nfce->infNFe->ide->mod);
        $this->assertSame('4.00', (string) $nfce->infNFe['versao']);
        $this->assertNotEmpty((string) $nfce->Signature->SignatureValue);
        $this->assertStringContainsString('qrcode?p=', (string) $nfce->infNFeSupl->qrCode);
    }

    public function test_document_is_always_marked_as_homologation(): void
    {
        $nfce = $this->parse($this->build());

        $this->assertSame('2', (string) $nfce->infNFe->ide->tpAmb);
        $this->assertSame(NfceXmlBuilder::HOMOLOGATION_ITEM, (string) $nfce->infNFe->det[0]->prod->xProd);
        $this->assertSame('REFRIGERANTE LATA 350ML', (string) $nfce->infNFe->det[1]->prod->xProd);
        $this->assertStringContainsString('SEM VALOR FISCAL', (string) $nfce->infNFe->infAdic->infCpl);
    }

    public function test_access_key_follows_the_official_composition(): void
    {
        $nfce = $this->parse($this->build());
        $key = substr((string) $nfce->infNFe['Id'], 3);

        // UF + AAMM + CNPJ + modelo + série + número + tipo de emissão + código + DV
        $this->assertSame('35'.'2610'.'11222333000181'.'65'.'001'.'000000042'.'1'.'48291736', substr($key, 0, 43));
        $this->assertSame($this->checkDigit(substr($key, 0, 43)), (int) $key[43]);
        $this->assertSame($key[43], (string) $nfce->infNFe->ide->cDV);
    }

    public function test_totals_match_the_sum_of_the_items(): void
    {
        $nfce = $this->parse($this->build());

        $this->assertSame('100.00', (string) $nfce->infNFe->det[0]->prod->vProd);
        $this->assertSame('9.00', (string) $nfce->infNFe->det[1]->prod->vProd);
        $this->assertSame('109.00', (string) $nfce->infNFe->total->ICMSTot->vProd);
        $this->assertSame('109.00', (string) $nfce->infNFe->total->ICMSTot->vNF);
        $this->assertSame('120.00', (string) $nfce->infNFe->pag->detPag->vPag);
        $this->assertSame('11.00', (string) $nfce->infNFe->pag->vTroco);
    }

    public function test_items_carry_their_tax_codes(): void
    {
        $nfce = $this->parse($this->build());
        [$meal, $drink] = [$nfce->infNFe->det[0], $nfce->infNFe->det[1]];

        $this->assertSame('21069090', (string) $meal->prod->NCM);
        $this->assertSame('5101', (string) $meal->prod->CFOP);
        $this->assertSame('102', (string) $meal->imposto->ICMS->ICMSSN102->CSOSN);

        $this->assertSame('5405', (string) $drink->prod->CFOP);
        $this->assertSame('0301000', (string) $drink->prod->CEST);
        $this->assertSame('500', (string) $drink->imposto->ICMS->ICMSSN500->CSOSN);
    }

    public function test_emission_time_uses_the_sao_paulo_timezone(): void
    {
        $nfce = $this->parse($this->build());

        // 15:30 UTC = 12:30 em São Paulo
        $this->assertSame('2026-10-05T12:30:00-03:00', (string) $nfce->infNFe->ide->dhEmi);
    }

    public function test_consumer_cpf_is_included_when_informed(): void
    {
        $nfce = $this->parse($this->build(['consumer_cpf' => '52998224725']));

        $this->assertSame('52998224725', (string) $nfce->infNFe->dest->CPF);
    }

    public function test_rejects_an_invoice_that_violates_the_xsd(): void
    {
        $invoice = $this->invoice();
        $invoice['items'][0]['ncm'] = 'ABC';

        $this->expectException(Throwable::class);

        $this->app->make(NfceXmlBuilder::class)->build($invoice);
    }

    public function test_command_generates_the_test_certificate(): void
    {
        unlink($this->certificatePath);

        $this->artisan('fiscal:test-certificate')->assertSuccessful();

        $this->assertFileExists($this->certificatePath);
    }

    private function build(array $overrides = []): string
    {
        return $this->app->make(NfceXmlBuilder::class)->build([...$this->invoice(), ...$overrides]);
    }

    private function invoice(): array
    {
        return [
            'number' => 42,
            'code' => '48291736',
            'issued_at' => Carbon::parse('2026-10-05 15:30:00', 'UTC'),
            'items' => [
                [
                    'code' => '99',
                    'description' => 'YAKISOBA CLASSICO GRANDE',
                    'ncm' => '21069090',
                    'cfop' => '5101',
                    'csosn' => '102',
                    'unit' => 'UN',
                    'quantity' => 2,
                    'unit_price' => 50.00,
                ],
                [
                    'code' => 'B1',
                    'description' => 'REFRIGERANTE LATA 350ML',
                    'ncm' => '22021000',
                    'cfop' => '5405',
                    'csosn' => '500',
                    'cest' => '0301000',
                    'unit' => 'UN',
                    'quantity' => 1,
                    'unit_price' => 9.00,
                ],
            ],
            'payments' => [['method' => '01', 'amount' => 120.00]],
            'change' => 11.00,
        ];
    }

    private function parse(string $xml): SimpleXMLElement
    {
        return new SimpleXMLElement($xml);
    }

    /**
     * Módulo 11 com pesos de 2 a 9, da direita para a esquerda.
     */
    private function checkDigit(string $digits): int
    {
        $sum = 0;
        $weight = 2;

        foreach (array_reverse(str_split($digits)) as $digit) {
            $sum += (int) $digit * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
