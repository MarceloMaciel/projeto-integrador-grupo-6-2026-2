<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\Fiscal\NfceXmlBuilder;
use App\Services\Fiscal\TestCertificate;
use Database\Seeders\MenuSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use SimpleXMLElement;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
    }

    private function sell(ProductVariation $variation, int $quantity = 1): Order
    {
        $item = OrderItem::fromVariation($variation, $quantity);

        $order = Order::create([
            'status' => Order::STATUS_CLOSED,
            'total' => $item->total,
            'payment_method' => '17',
            'amount_received' => $item->total,
            'closed_at' => now(),
        ]);
        $order->items()->save($item);

        return $order;
    }

    public function test_item_copies_name_price_and_tax_data(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        $variation = $product->variations->firstWhere('name', 'Grande');

        $item = $this->sell($variation, 2)->items->first();

        $this->assertSame('38', $item->code);
        $this->assertSame('Carnes c/ Legumes Especial - Grande', $item->description);
        $this->assertSame([$product->ncm, $product->cfop, $product->csosn, $product->cest, $product->unit], [$item->ncm, $item->cfop, $item->csosn, $item->cest, $item->unit]);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('50.00', $item->unit_price);
        $this->assertSame('100.00', $item->total);
    }

    public function test_changing_the_menu_does_not_change_past_items(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        $variation = $product->variations->first();
        $order = $this->sell($variation);

        $product->update(['name' => 'Outro nome', 'ncm' => '99999999']);
        $variation->update(['price' => 999]);

        $item = $order->items()->first();

        $this->assertSame('21069090', $item->ncm);
        $this->assertSame('50.00', $item->unit_price);
        $this->assertStringStartsWith('Carnes c/ Legumes Especial', $item->description);
    }

    public function test_code_falls_back_from_variation_to_product_to_id(): void
    {
        $dish = Product::byCode('38')->firstOrFail();
        $beverage = Product::where('name', 'Cerveja Lata 350ml')->firstOrFail();
        $variation = $dish->variations->first();

        $this->assertSame('38', OrderItem::fromVariation($variation, 1)->code);
        $this->assertSame((string) $beverage->id, OrderItem::fromVariation($beverage->variations->first(), 1)->code);

        $variation->code = '38-G';
        $this->assertSame('38-G', OrderItem::fromVariation($variation, 1)->code);
    }

    public function test_description_skips_a_repeated_variation_name_and_fits_the_xml(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        $variation = $product->variations->first();

        $variation->name = $product->name;
        $this->assertSame($product->name, OrderItem::fromVariation($variation, 1)->description);
        $this->assertSame('Cerveja Lata 350ml', OrderItem::describe('Cerveja Lata 350ml', 'Lata 350ml'));
        $this->assertSame('Filé de Peixe', OrderItem::describe('Filé de Peixe', 'Padrão'));

        $product->name = str_repeat('a', 200);
        $variation->setRelation('product', $product);
        $this->assertSame(120, mb_strlen(OrderItem::fromVariation($variation, 1)->description));
    }

    public function test_database_refuses_to_delete_a_sold_variation(): void
    {
        $variation = Product::byCode('38')->firstOrFail()->variations->first();
        $this->sell($variation);

        $this->expectException(QueryException::class);

        $variation->delete();
    }

    public function test_deleting_an_order_removes_its_items(): void
    {
        $order = $this->sell(Product::byCode('38')->firstOrFail()->variations->first());
        $order->delete();

        $this->assertSame(0, OrderItem::count());
    }

    public function test_sold_product_cannot_be_deleted_through_the_screen(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        $this->sell($product->variations->first());

        $this->actingAs(User::factory()->create())
            ->delete(route('menu.products.destroy', $product))
            ->assertRedirect(route('menu.products.edit', $product))
            ->assertSessionHas('error');

        $this->assertModelExists($product);
    }

    public function test_removing_a_sold_variation_on_the_form_deactivates_it(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        [$sold, $kept] = $product->variations;
        $this->sell($sold);

        $this->actingAs(User::factory()->create())->put(route('menu.products.update', $product), [
            'category_id' => $product->category_id,
            'name' => $product->name,
            'is_active' => '1',
            'ncm' => $product->ncm,
            'cfop' => $product->cfop,
            'csosn' => $product->csosn,
            'unit' => $product->unit,
            'variations' => [['id' => $kept->id, 'name' => $kept->name, 'price' => $kept->price, 'is_active' => '1']],
        ])->assertSessionHasNoErrors()->assertSessionHas('status');

        $this->assertModelExists($sold);
        $this->assertFalse($sold->refresh()->is_active);
    }

    public function test_items_feed_the_xml_builder_without_conversion(): void
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

        $item = OrderItem::fromVariation(Product::where('name', 'Cerveja Lata 350ml')->firstOrFail()->variations->first(), 2);

        try {
            $xml = $this->app->make(NfceXmlBuilder::class)->build([
                'number' => 1,
                'code' => '48291736',
                'issued_at' => Carbon::parse('2026-10-31 15:30:00', 'UTC'),
                'items' => [[
                    ...$item->only(['code', 'description', 'ncm', 'cfop', 'csosn', 'cest', 'unit', 'quantity']),
                    'unit_price' => (float) $item->unit_price,
                ]],
                'payments' => [['method' => '01', 'amount' => (float) $item->total]],
            ]);
        } finally {
            @unlink($certificatePath);
        }

        $this->assertSame((float) $item->total, (float) (new SimpleXMLElement($xml))->infNFe->total->ICMSTot->vNF);
    }
}
