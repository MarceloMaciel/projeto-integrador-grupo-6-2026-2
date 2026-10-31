<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_CPF = '52998224725';

    protected User $user;

    protected ProductVariation $dish;

    protected ProductVariation $beer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
        $this->user = User::factory()->create();
        // R$ 50,00 e o preço da cerveja em lata, lido do seeder.
        $this->dish = Product::byCode('38')->firstOrFail()->variations->firstWhere('name', 'Grande');
        $this->beer = Product::where('name', 'Cerveja Lata 350ml')->firstOrFail()->variations->first();
    }

    private function close(array $overrides = [])
    {
        return $this->actingAs($this->user)->post(route('orders.store'), [
            'items' => [
                ['variation_id' => $this->dish->id, 'quantity' => 2],
            ],
            'payment_method' => '17',
            ...$overrides,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('orders.create'))->assertRedirect(route('login'));
        $this->post(route('orders.store'))->assertRedirect(route('login'));
    }

    public function test_screen_lists_only_active_products_and_variations(): void
    {
        Product::where('name', 'Cerveja Lata 350ml')->update(['is_active' => false]);
        $inactive = Product::byCode('39')->firstOrFail()->variations->firstWhere('name', 'Média');
        $inactive->update(['is_active' => false]);

        $response = $this->actingAs($this->user)->get(route('orders.create'));

        $response->assertOk();
        $response->assertSee('Carnes c/ Legumes Especial');
        $response->assertDontSee('Cerveja Lata 350ml');
        $response->assertDontSee('data-variation-id="'.$inactive->id.'"', false);
    }

    public function test_closes_order_with_totals_and_copies(): void
    {
        $response = $this->close([
            'items' => [
                ['variation_id' => $this->dish->id, 'quantity' => 2],
                ['variation_id' => $this->beer->id, 'quantity' => 1],
            ],
        ]);

        $order = Order::with('items')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $expected = number_format(100 + (float) $this->beer->price, 2, '.', '');

        $this->assertSame(Order::STATUS_CLOSED, $order->status);
        $this->assertSame($expected, $order->total);
        $this->assertNotNull($order->closed_at);
        $this->assertCount(2, $order->items);
        $this->assertSame('5405', $order->items->firstWhere('product_variation_id', $this->beer->id)->cfop);

        $this->actingAs($this->user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Carnes c/ Legumes Especial - Grande')
            ->assertSee('PIX');
    }

    public function test_price_and_total_sent_by_the_browser_are_ignored(): void
    {
        $this->close([
            'total' => '1.00',
            'items' => [['variation_id' => $this->dish->id, 'quantity' => 1, 'unit_price' => '0.01']],
        ]);

        $order = Order::with('items')->firstOrFail();

        $this->assertSame('50.00', $order->total);
        $this->assertSame('50.00', $order->items->first()->unit_price);
    }

    public function test_cash_calculates_change_and_refuses_too_little(): void
    {
        $this->close(['payment_method' => '01', 'amount_received' => '99.99'])
            ->assertSessionHasErrors('amount_received');
        $this->close(['payment_method' => '01'])
            ->assertSessionHasErrors('amount_received');
        $this->assertSame(0, Order::count());

        $this->close(['payment_method' => '01', 'amount_received' => '120.00'])
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();

        $this->assertSame('120.00', $order->amount_received);
        $this->assertSame('20.00', $order->change);
    }

    public function test_other_methods_receive_exactly_the_total(): void
    {
        $this->close(['payment_method' => '17', 'amount_received' => '500.00']);

        $order = Order::firstOrFail();

        $this->assertSame('100.00', $order->amount_received);
        $this->assertSame('0.00', $order->change);
    }

    public function test_inactive_items_are_refused(): void
    {
        $this->dish->update(['is_active' => false]);
        $this->close()->assertSessionHasErrors('items');

        $this->dish->update(['is_active' => true]);
        $this->dish->product->update(['is_active' => false]);
        $this->close()->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
    }

    public function test_invalid_orders_are_refused(): void
    {
        $this->close(['items' => []])->assertSessionHasErrors('items');
        $this->close(['items' => [['variation_id' => 999999, 'quantity' => 1]]])->assertSessionHasErrors('items.0.variation_id');
        $this->close(['items' => [['variation_id' => $this->dish->id, 'quantity' => 0]]])->assertSessionHasErrors('items.0.quantity');
        $this->close(['payment_method' => '99'])->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
    }

    public function test_cpf_is_stored_without_mask_and_invalid_is_refused(): void
    {
        foreach (['111.111.111-11', '529.982.247-24', '123'] as $invalid) {
            $this->close(['consumer_cpf' => $invalid])->assertSessionHasErrors('consumer_cpf');
        }

        $this->close(['consumer_cpf' => '529.982.247-25'])->assertSessionHasNoErrors();

        $this->assertSame(self::VALID_CPF, Order::firstOrFail()->consumer_cpf);
    }

    public function test_repeated_variation_sums_quantities(): void
    {
        $this->close(['items' => [
            ['variation_id' => $this->dish->id, 'quantity' => 1],
            ['variation_id' => $this->dish->id, 'quantity' => 2],
        ]])->assertSessionHasNoErrors();

        $order = Order::with('items')->firstOrFail();

        $this->assertCount(1, $order->items);
        $this->assertSame(3, $order->items->first()->quantity);
        $this->assertSame('150.00', $order->total);
    }

    public function test_dashboard_and_navigation_link_to_the_screen(): void
    {
        $this->actingAs($this->user)->get(route('dashboard'))
            ->assertSee(route('orders.create'))
            ->assertSee('Criar pedido');
    }
}
