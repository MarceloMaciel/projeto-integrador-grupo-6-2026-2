<?php

namespace Tests\Feature\Fiscal;

use App\Models\Category;
use App\Models\Product;
use App\Services\Fiscal\NfceXmlBuilder;
use App\Services\Fiscal\TestCertificate;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use SimpleXMLElement;
use Tests\TestCase;

class ProductFiscalDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
    }

    public function test_dishes_use_the_prepared_profile(): void
    {
        $dish = Product::byCode('38')->firstOrFail();

        $this->assertSame('21069090', $dish->ncm);
        $this->assertSame('5101', $dish->cfop);
        $this->assertSame('102', $dish->csosn);
        $this->assertNull($dish->cest);
        $this->assertSame('UN', $dish->unit);
    }

    public function test_beverages_use_their_packaging_profile(): void
    {
        $expected = [
            'Coca-Cola 2L' => ['22021000', '5405', '500', '0301001'],
            'Refrigerante Lata 350ml' => ['22021000', '5405', '500', '0301002'],
            'Cerveja Garrafa 600ml' => ['22030000', '5405', '500', '0302100'],
            'Cerveja Lata 350ml' => ['22030000', '5405', '500', '0302103'],
            'Água Mineral 500ml' => ['22011000', '5405', '500', '0300504'],
            'Suco Lata Del Valle' => ['22029900', '5102', '102', null],
        ];

        foreach ($expected as $name => [$ncm, $cfop, $csosn, $cest]) {
            $product = Product::where('name', $name)->firstOrFail();

            $this->assertSame([$ncm, $cfop, $csosn, $cest], [$product->ncm, $product->cfop, $product->csosn, $product->cest], $name);
        }
    }

    public function test_only_beverages_leave_the_prepared_profile(): void
    {
        $beverages = Category::where('slug', 'bebidas')->firstOrFail();

        $this->assertSame(0, Product::where('category_id', '!=', $beverages->id)->where('ncm', '!=', '21069090')->count());
        $this->assertSame(0, Product::where('category_id', $beverages->id)->where('ncm', '21069090')->count());
    }

    public function test_every_product_has_well_formed_tax_codes(): void
    {
        foreach (Product::all() as $product) {
            $this->assertMatchesRegularExpression('/^\d{8}$/', $product->ncm, $product->name);
            $this->assertMatchesRegularExpression('/^\d{4}$/', $product->cfop, $product->name);
            $this->assertContains($product->csosn, ['102', '500'], $product->name);
            // CEST existe só para itens com substituição tributária (CSOSN 500).
            if ($product->csosn === '500') {
                $this->assertMatchesRegularExpression('/^\d{7}$/', (string) $product->cest, $product->name);
            } else {
                $this->assertNull($product->cest, $product->name);
            }
        }
    }

    public function test_every_profile_in_the_config_is_used_by_the_menu(): void
    {
        $profiles = config('fiscal.product_profiles');

        foreach ($profiles as $name => $profile) {
            $this->assertTrue(
                Product::where('ncm', $profile['ncm'])->where('cfop', $profile['cfop'])->where('cest', $profile['cest'])->exists(),
                "Perfil fiscal sem uso: {$name}"
            );
        }
    }

    public function test_products_from_the_database_build_a_valid_nfce(): void
    {
        $certificatePath = sys_get_temp_dir().'/nfce-test-'.uniqid().'.pfx';
        // Os testes não podem depender do emitente configurado no .env de cada máquina.
        config([
            'fiscal.certificate.path' => $certificatePath,
            'fiscal.series' => 1,
            'fiscal.emitter.cnpj' => '11222333000181',
            'fiscal.emitter.state_registration' => '123456789012',
            'fiscal.emitter.legal_name' => 'EMPRESA DE TESTE LTDA',
            'fiscal.emitter.trade_name' => 'EMPRESA DE TESTE',
        ]);
        $this->app->make(TestCertificate::class)->generate('EMPRESA DE TESTE LTDA', '11222333000181');

        $dish = Product::byCode('99')->firstOrFail();
        $beer = Product::where('name', 'Cerveja Lata 350ml')->firstOrFail();

        try {
            // signNFe() valida contra o XSD e lança exceção se o XML for inválido.
            $xml = $this->app->make(NfceXmlBuilder::class)->build([
                'number' => 1,
                'code' => '48291736',
                'issued_at' => Carbon::parse('2026-10-18 15:30:00', 'UTC'),
                'items' => [$this->item($dish, 50.00), $this->item($beer, 8.00)],
                'payments' => [['method' => '01', 'amount' => 58.00]],
                'change' => 0,
            ]);
        } finally {
            @unlink($certificatePath);
        }

        $nfce = new SimpleXMLElement($xml);
        [$meal, $drink] = [$nfce->infNFe->det[0], $nfce->infNFe->det[1]];

        $this->assertSame('21069090', (string) $meal->prod->NCM);
        $this->assertSame('5101', (string) $meal->prod->CFOP);
        $this->assertSame('22030000', (string) $drink->prod->NCM);
        $this->assertSame('5405', (string) $drink->prod->CFOP);
        $this->assertSame('0302103', (string) $drink->prod->CEST);
        $this->assertSame('500', (string) $drink->imposto->ICMS->ICMSSN500->CSOSN);
    }

    private function item(Product $product, float $price): array
    {
        return [
            'code' => $product->code ?? (string) $product->id,
            'description' => mb_strtoupper($product->name),
            'ncm' => $product->ncm,
            'cfop' => $product->cfop,
            'csosn' => $product->csosn,
            'cest' => $product->cest,
            'unit' => $product->unit,
            'quantity' => 1,
            'unit_price' => $price,
        ];
    }
}
