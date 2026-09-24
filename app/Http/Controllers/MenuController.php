<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Exibe a listagem do cardápio do restaurante com filtros de busca e categoria.
     */
    public function index(Request $request): View
    {
        $selectedCategorySlug = $request->query('categoria');
        $searchQuery = trim($request->query('busca', ''));

        $categories = Category::active()->ordered()->withCount('allProducts')->get();

        $productsQuery = Product::active()
            ->with(['category', 'categories', 'variations' => function ($query) {
                $query->active()->ordered();
            }])
            ->ordered();

        // Filtro por categoria
        if (!empty($selectedCategorySlug)) {
            $productsQuery->whereHas('categories', function ($q) use ($selectedCategorySlug) {
                $q->where('slug', $selectedCategorySlug);
            });
        }

        // Filtro por busca textual (nome, código ou ingredientes/descrição)
        if (!empty($searchQuery)) {
            $productsQuery->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('code', 'like', "%{$searchQuery}%")
                    ->orWhere('description', 'like', "%{$searchQuery}%")
                    ->orWhereHas('variations', function ($vq) use ($searchQuery) {
                        $vq->where('name', 'like', "%{$searchQuery}%")
                            ->orWhere('description', 'like', "%{$searchQuery}%");
                    });
            });
        }

        $products = $productsQuery->get();

        // Agrupamento por categoria caso não haja busca ativa e nenhuma categoria específica selecionada
        $groupedByCategory = false;
        $categoriesWithProducts = collect();

        if (empty($selectedCategorySlug) && empty($searchQuery)) {
            $groupedByCategory = true;
            $categoriesWithProducts = Category::active()
                ->ordered()
                ->with(['allProducts' => function ($query) {
                    $query->active()->ordered()->with(['variations' => function ($q) {
                        $q->active()->ordered();
                    }]);
                }])
                ->get();
        }

        $totalCategoriesCount = $categories->count();
        $totalProductsCount = Product::active()->count();
        $totalVariationsCount = ProductVariation::active()->count();

        return view('menu.index', [
            'categories' => $categories,
            'products' => $products,
            'groupedByCategory' => $groupedByCategory,
            'categoriesWithProducts' => $categoriesWithProducts,
            'selectedCategorySlug' => $selectedCategorySlug,
            'searchQuery' => $searchQuery,
            'totalCategoriesCount' => $totalCategoriesCount,
            'totalProductsCount' => $totalProductsCount,
            'totalVariationsCount' => $totalVariationsCount,
        ]);
    }
}
