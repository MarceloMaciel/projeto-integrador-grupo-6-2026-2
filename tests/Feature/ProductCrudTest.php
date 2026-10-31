<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
        $this->user = User::factory()->create();
        $this->category = Category::where('slug', 'yakisoba')->firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        return [
            'category_id' => $this->category->id,
            'code' => '777',
            'name' => 'Prato Novo',
            'description' => 'Descrição do prato.',
            'is_active' => '1',
            'ncm' => '2106.90.90',
            'cfop' => '5.101',
            'csosn' => '102',
            'cest' => '',
            'unit' => 'UN',
            'variations' => [
                ['name' => 'Grande', 'price' => '50.00', 'is_active' => '1'],
                ['name' => 'Média', 'price' => '38.50', 'is_active' => '1'],
            ],
            ...$overrides,
        ];
    }

    public function test_guests_cannot_reach_the_form(): void
    {
        $this->get(route('menu.products.create'))->assertRedirect(route('login'));
        $this->post(route('menu.products.store'), $this->payload())->assertRedirect(route('login'));
    }

    public function test_create_form_offers_the_fiscal_profiles_with_prepared_codes(): void
    {
        $response = $this->actingAs($this->user)->get(route('menu.products.create'));

        $response->assertOk();
        $response->assertSee('Cerveja em lata');
        $response->assertSee('value="21069090"', false);
    }

    public function test_creates_product_with_variations_and_clean_tax_codes(): void
    {
        $this->actingAs($this->user)->post(route('menu.products.store'), $this->payload())
            ->assertRedirect(route('menu.index'));

        $product = Product::where('name', 'Prato Novo')->firstOrFail();

        $this->assertSame('prato-novo', $product->slug);
        $this->assertSame('21069090', $product->ncm);
        $this->assertSame('5101', $product->cfop);
        $this->assertNull($product->cest);
        $this->assertSame(['Grande', 'Média'], $product->variations->pluck('name')->all());
        $this->assertSame('38.50', $product->variations[1]->price);
    }

    public function test_new_product_appears_in_its_category_on_the_menu(): void
    {
        $this->actingAs($this->user)->post(route('menu.products.store'), $this->payload());

        $this->get(route('menu.index'))->assertSee('Prato Novo');
        $this->get(route('menu.index', ['categoria' => 'yakisoba']))->assertSee('Prato Novo');
    }

    public function test_repeated_names_get_unique_slugs(): void
    {
        $this->actingAs($this->user);
        $this->post(route('menu.products.store'), $this->payload());
        $this->post(route('menu.products.store'), $this->payload());

        $this->assertSame(
            ['prato-novo', 'prato-novo-2'],
            Product::where('name', 'Prato Novo')->orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_cest_is_required_for_tax_substitution_and_refused_otherwise(): void
    {
        $this->actingAs($this->user);

        $this->post(route('menu.products.store'), $this->payload(['csosn' => '500', 'cest' => '']))
            ->assertSessionHasErrors('cest');
        $this->post(route('menu.products.store'), $this->payload(['csosn' => '102', 'cest' => '0301001']))
            ->assertSessionHasErrors('cest');
        $this->post(route('menu.products.store'), $this->payload(['csosn' => '500', 'cest' => '03.010.01']))
            ->assertSessionHasNoErrors();
    }

    public function test_invalid_data_is_refused(): void
    {
        $this->actingAs($this->user);

        $this->post(route('menu.products.store'), $this->payload(['ncm' => '123']))
            ->assertSessionHasErrors('ncm');
        $this->post(route('menu.products.store'), $this->payload(['variations' => []]))
            ->assertSessionHasErrors('variations');
        $this->post(route('menu.products.store'), $this->payload([
            'variations' => [['name' => 'Grande', 'price' => '0', 'is_active' => '1']],
        ]))->assertSessionHasErrors('variations.0.price');
    }

    public function test_update_keeps_adds_and_removes_variations(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        [$grande, $media] = $product->variations;

        $this->actingAs($this->user)->put(route('menu.products.update', $product), $this->payload([
            'name' => 'Nome Editado',
            'variations' => [
                ['id' => $grande->id, 'name' => 'Grande', 'price' => '60.00', 'is_active' => '1'],
                ['name' => 'Família', 'price' => '80.00', 'is_active' => '1'],
            ],
        ]))->assertRedirect(route('menu.index'));

        $product->refresh();

        $this->assertSame('Nome Editado', $product->name);
        $this->assertStringNotContainsString('nome-editado', $product->slug);
        $this->assertSame('60.00', $grande->refresh()->price);
        $this->assertModelMissing($media);
        $this->assertSame(['Grande', 'Família'], $product->variations->pluck('name')->all());
    }

    public function test_changing_category_moves_only_the_primary_association(): void
    {
        $product = Product::where('code', '96')->firstOrFail();
        $previous = $product->category_id;
        $vegetarian = Category::where('slug', 'vegetariano')->firstOrFail();
        $rice = Category::where('slug', 'arroz')->firstOrFail();

        $this->actingAs($this->user)->put(route('menu.products.update', $product), $this->payload([
            'category_id' => $rice->id,
            'variations' => $product->variations->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $v->price, 'is_active' => '1'])->all(),
        ]))->assertSessionHasNoErrors();

        $categoryIds = $product->categories()->pluck('categories.id')->all();

        $this->assertContains($rice->id, $categoryIds);
        $this->assertContains($vegetarian->id, $categoryIds);
        $this->assertNotContains($previous, $categoryIds);
    }

    public function test_cannot_update_a_variation_of_another_product(): void
    {
        $product = Product::byCode('38')->firstOrFail();
        $other = Product::byCode('39')->firstOrFail()->variations->first();

        $this->actingAs($this->user)->put(route('menu.products.update', $product), $this->payload([
            'variations' => [['id' => $other->id, 'name' => 'Invadida', 'price' => '1.00', 'is_active' => '1']],
        ]))->assertSessionHasErrors('variations.0.id');

        $this->assertNotSame('Invadida', $other->refresh()->name);
    }

    public function test_inactive_product_leaves_the_menu_and_returns_with_the_filter(): void
    {
        $product = Product::byCode('38')->firstOrFail();

        $this->actingAs($this->user)->put(route('menu.products.update', $product), $this->payload([
            'name' => 'Prato Desativado',
            'is_active' => '0',
            'variations' => [['name' => 'Grande', 'price' => '50.00', 'is_active' => '1']],
        ]));

        $this->get(route('menu.index'))->assertDontSee('Prato Desativado');
        $this->get(route('menu.index', ['inativos' => 1]))
            ->assertSee('Prato Desativado')
            ->assertSee('Inativo');
    }

    public function test_deletes_a_product_never_sold(): void
    {
        $product = Product::byCode('38')->firstOrFail();

        $this->actingAs($this->user)->delete(route('menu.products.destroy', $product))
            ->assertRedirect(route('menu.index'));

        $this->assertModelMissing($product);
    }

    public function test_edit_form_shows_the_product_and_its_profile(): void
    {
        $beer = Product::where('name', 'Cerveja Lata 350ml')->firstOrFail();

        $response = $this->actingAs($this->user)->get(route('menu.products.edit', $beer));

        $response->assertOk();
        $response->assertSee('value="0302103"', false);
        $response->assertSee('<option value="beer_can" selected>', false);
    }
}
