<?php
// app/Http/Controllers/Api/CheckoutController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use ApiResponse;

    public function __construct(protected OrderService $orderService) {}

    /**
     * GET /api/checkout
     */
    public function index()
    {
        $cart = auth()->user()->cart()->with('items.book')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->errorResponse('Keranjang kosong.', 422);
        }

        $subtotal = $cart->items->sum(fn($i) => $i->book->display_price * $i->quantity);

        return $this->successResponse([
            'items' => $cart->items->map(fn($i) => [
                'book_title' => $i->book->title,
                'quantity' => $i->quantity,
                'price' => $i->book->display_price,
                'subtotal' => $i->book->display_price * $i->quantity,
            ]),
            'subtotal' => (float) $subtotal,
            'shipping_cost' => 0,
            'total' => (float) $subtotal,
        ]);
    }

    /**
     * POST /api/checkout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->orderService->createOrder(auth()->user(), $validated);

            return $this->createdResponse(
                new OrderResource($order->load('items')),
                'Pesanan berhasil dibuat! Silakan lakukan pembayaran.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}