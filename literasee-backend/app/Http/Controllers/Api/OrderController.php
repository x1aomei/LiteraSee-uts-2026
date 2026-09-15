<?php
// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $orders = auth()->user()->orders()
            ->with('items')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->per_page ?? 10);

        return $this->successResponse(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    /**
     * GET /api/orders/{order}
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Akses ditolak.', 403);
        }
        $order->load(['items', 'payment']);
        return $this->successResponse(new OrderResource($order));
    }

    /**
     * POST /api/orders/{order}/cancel
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return $this->errorResponse('Pesanan tidak bisa dibatalkan.', 422);
        }

        foreach ($order->items as $item) {
            $item->book?->increment('stock', $item->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return $this->successResponse(
            new OrderResource($order->fresh()),
            'Pesanan dibatalkan.'
        );
    }
}