<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MenuSeeder::class);
    }

    public function test_categories_are_properly_seeded(): void
    {
        $this->assertDatabaseCount('categories', 10);

        $expectedCategories = [
            'Carne',
            'Frutos do Mar',
            'Bebidas',
            'Adicionais',
            'Entradas e Porções',
            'Yakisoba',
            'Frango',
            'Lombo',
            'Arroz',
            'Vegetariano',
        ];

        foreach ($expectedCategories as $catName) {
            $this->assertDatabaseHas('categories', ['name' => $catName]);
        }
    }

    public function test_products_and_variations_are_properly_seeded(): void
    {
        $this->assertDatabaseCount('products', 59);
        $this->assertDatabaseCount('product_variations', 92);
    }

    public function test_product_belongs_to_category_and_has_variations(): void
    {
        $yakisoba = Product::where('code', '99')->first();

        $this->assertNotNull($yakisoba);
        $this->assertEquals('Yakisoba Clássico', $yakisoba->name);
        $this->assertEquals('Yakisoba', $yakisoba->category->name);
        $this->assertCount(2, $yakisoba->variations);

        $grande = $yakisoba->variations->where('name', 'Grande')->first();
        $this->assertNotNull($grande);
        $this->assertEquals('50.00', $grande->price);
        $this->assertEquals('R$ 50,00', $grande->formatted_price);

        $media = $yakisoba->variations->where('name', 'Média')->first();
        $this->assertNotNull($media);
        $this->assertEquals('37.00', $media->price);
        $this->assertEquals('R$ 37,00', $media->formatted_price);

        $this->assertEquals(37.00, $yakisoba->min_price);
        $this->assertEquals(50.00, $yakisoba->max_price);
        $this->assertEquals('R$ 37,00 - R$ 50,00', $yakisoba->formatted_price_range);
    }

    public function test_product_query_scopes(): void
    {
        $activeProducts = Product::active()->ordered()->get();
        $this->assertCount(59, $activeProducts);

        $productByCode = Product::byCode('38')->first();
        $this->assertNotNull($productByCode);
        $this->assertEquals('Carnes c/ Legumes Especial', $productByCode->name);
    }

    public function test_category_relationships(): void
    {
        $carneCategory = Category::where('slug', 'carne')->first();
        $this->assertNotNull($carneCategory);
        $this->assertCount(6, $carneCategory->products);

        $vegCategory = Category::where('slug', 'vegetariano')->first();
        $this->assertNotNull($vegCategory);
        $this->assertTrue($vegCategory->allProducts()->count() >= 2);
    }
}
