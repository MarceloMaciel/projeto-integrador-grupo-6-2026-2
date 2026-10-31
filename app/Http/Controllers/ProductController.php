<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Exibe o formulário de produto novo, com os dados fiscais do perfil padrão.
     */
    public function create(): View
    {
        $product = new Product([
            ...config('fiscal.product_profiles.prepared'),
            'is_active' => true,
        ]);

        return $this->form($product);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $product = new Product($this->productData($request));
            $product->slug = $this->uniqueSlug($product->name);
            $product->order = (int) Product::where('category_id', $product->category_id)->max('order') + 1;
            $product->save();

            $product->categories()->syncWithoutDetaching([$product->category_id]);
            $this->saveVariations($product, $request->validated('variations'));
        });

        return redirect()->route('menu.index')->with('status', 'Produto criado.');
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load('variations'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $deactivated = DB::transaction(function () use ($request, $product) {
            $previousCategoryId = $product->category_id;
            $product->update($this->productData($request));

            // Troca só a categoria principal no pivô; as demais associações ficam.
            if ($previousCategoryId !== $product->category_id) {
                $product->categories()->detach($previousCategoryId);
            }
            $product->categories()->syncWithoutDetaching([$product->category_id]);

            return $this->saveVariations($product, $request->validated('variations'));
        });

        $message = 'Produto atualizado.';
        if ($deactivated > 0) {
            $message .= ' Variações que já foram vendidas não podem ser apagadas e foram desativadas.';
        }

        return redirect()->route('menu.index')->with('status', $message);
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            DB::transaction(fn () => $product->delete());
        } catch (QueryException) {
            return redirect()->route('menu.products.edit', $product)
                ->with('error', 'Este produto já foi vendido e não pode ser apagado. Para tirá-lo do cardápio, desmarque "Produto ativo".');
        }

        return redirect()->route('menu.index')->with('status', 'Produto excluído.');
    }

    private function form(Product $product): View
    {
        return view('menu.form', [
            'product' => $product,
            'categories' => Category::active()->ordered()->get(),
            'fiscalProfiles' => config('fiscal.product_profiles'),
        ]);
    }

    private function productData(ProductRequest $request): array
    {
        return collect($request->validated())->except('variations')->all();
    }

    /**
     * Grava as variações do formulário e remove as que saíram dele. Devolve quantas
     * não puderam ser apagadas por já terem sido vendidas (ficam desativadas).
     */
    private function saveVariations(Product $product, array $rows): int
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $attributes = [
                'name' => $row['name'],
                'price' => $row['price'],
                'is_active' => $row['is_active'],
                'order' => $index + 1,
            ];

            $keptIds[] = empty($row['id'])
                ? $product->variations()->create($attributes)->id
                : tap(ProductVariation::findOrFail($row['id']))->update($attributes)->id;
        }

        $deactivated = 0;

        foreach ($product->variations()->whereNotIn('id', $keptIds)->get() as $variation) {
            try {
                // Transação aninhada: no Postgres, um erro fora de um savepoint invalida a externa.
                DB::transaction(fn () => $variation->delete());
            } catch (QueryException) {
                $variation->update(['is_active' => false]);
                $deactivated++;
            }
        }

        return $deactivated;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
