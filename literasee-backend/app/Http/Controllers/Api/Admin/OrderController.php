<?php
// app/Http/Controllers/Api/Admin/OrderController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn($q, $s) => $q->where('payment_status', $s))
            ->when($request->q, fn($q, $kw) => $q->where('order_number', 'like', "%{$kw}%"))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return $this->successResponse(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    public function show(Order $order)
    {
        $order->load(['items.book', 'user', 'payment']);
        return $this->successResponse(new OrderResource($order));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // Restock jika dibatalkan
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->book?->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $newStatus]);

        return $this->successResponse(
            new OrderResource($order->fresh()),
            "Status pesanan diubah ke {$newStatus}."
        );
    }
}