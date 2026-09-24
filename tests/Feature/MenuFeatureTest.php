<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('menu.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_menu_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.index'));

        $response->assertOk();
        $response->assertViewIs('menu.index');
        $response->assertSee('Cardápio do Restaurante');
        $response->assertSee('Yakisoba Clássico');
        $response->assertSee('Carnes c/ Legumes Especial');
        $response->assertSee('R$ 50,00');
    }

    public function test_can_filter_menu_by_category(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.index', ['categoria' => 'yakisoba']));

        $response->assertOk();
        $response->assertSee('Yakisoba Clássico');
        $response->assertSee('Yakisoba Especial');
        $response->assertSee('Yakisoba Camarão');
        $response->assertDontSee('Filé de Peixe ao Molho de Gengibre');
    }

    public function test_can_search_menu_by_code(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.index', ['busca' => '99']));

        $response->assertOk();
        $response->assertSee('Yakisoba Clássico');
        $response->assertSee('#99');
        $response->assertDontSee('Filé de Peixe ao Molho de Gengibre');
    }

    public function test_can_search_menu_by_name(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.index', ['busca' => 'Guioza']));

        $response->assertOk();
        $response->assertSee('Guioza Suíno');
        $response->assertSee('#10');
        $response->assertDontSee('Yakisoba Clássico');
    }

    public function test_can_search_menu_by_ingredient(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.index', ['busca' => 'champignon']));

        $response->assertOk();
        $response->assertSee('Carne com Brócolis Especial');
        $response->assertSee('Yakisoba Especial');
    }

    public function test_shows_empty_state_when_no_results_found(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.index', ['busca' => 'item_totalmente_inexistente_xyz']));

        $response->assertOk();
        $response->assertSee('Nenhum item encontrado');
        $response->assertSee('Ver todo o cardápio');
    }

    public function test_dashboard_contains_link_to_menu(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('menu.index'));
        $response->assertSee('Cardápio');
        $response->assertSee('Acessar cardápio');
    }
}
