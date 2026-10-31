<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Exibe a montagem do pedido com os produtos e variações ativos.
     */
    public function create(): View
    {
        $categories = Category::active()
            ->ordered()
            ->with(['products' => function ($query) {
                $query->active()->ordered()->with(['variations' => function ($q) {
                    $q->active()->ordered();
                }]);
            }])
            ->get();

        return view('orders.create', [
            'categories' => $categories,
            'paymentMethods' => config('fiscal.payment_methods'),
            'cashPaymentMethod' => config('fiscal.cash_payment_method'),
        ]);
    }

    /**
     * O pedido nasce fechado: itens e pagamento são gravados de uma vez.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $total = $request->total();
        $received = $request->isCash() ? (float) $request->validated('amount_received') : $total;

        $order = DB::transaction(function () use ($request, $total, $received) {
            $order = Order::create([
                'status' => Order::STATUS_CLOSED,
                'total' => $total,
                'payment_method' => $request->validated('payment_method'),
                'amount_received' => $received,
                'change' => round($received - $total, 2),
                'consumer_cpf' => $request->validated('consumer_cpf'),
                'closed_at' => now(),
            ]);
            $order->items()->saveMany($request->orderItems());

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('status', 'Pedido fechado.');
    }

    public function show(Order $order): View
    {
        return view('orders.show', ['order' => $order->load('items')]);
    }
}
