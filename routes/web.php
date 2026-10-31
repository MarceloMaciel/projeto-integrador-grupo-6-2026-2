<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// O sistema é de uso interno: a raiz leva direto ao painel, que por sua vez
// exige autenticação (visitante não logado cai na tela de login).
Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/cardapio', [MenuController::class, 'index'])->name('menu.index');
    Route::resource('/cardapio/produtos', ProductController::class)
        ->except(['index', 'show'])
        ->parameters(['produtos' => 'product'])
        ->names('menu.products');
    Route::get('/pedidos/novo', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/pedidos', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
