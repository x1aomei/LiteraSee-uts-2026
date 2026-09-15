<?php
// app/Services/OrderService.php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function createOrder(User $user, array $shippingData): Order
    {
        $cart = $user->cart()->with('items.book')->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($user, $cart, $shippingData) {

            // 1. VALIDASI STOK
            $totalAmount = 0;
            foreach ($cart->items as $item) {
                if ($item->quantity > $item->book->stock) {
                    throw new \Exception("Stok produk {$item->book->title} tidak mencukupi.");
                }
                $totalAmount += $item->book->display_price * $item->quantity;
            }

            // 2. BUAT ORDER
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_name' => $shippingData['name'],
                'shipping_phone' => $shippingData['phone'],
                'shipping_address' => $shippingData['address'],
                'notes' => $shippingData['notes'] ?? null,
                'total_amount' => $totalAmount,
                'shipping_cost' => 0,
            ]);

            // 3. PINDAHKAN ITEMS (SNAPSHOT + ATOMIC STOCK)
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'book_id' => $item->book_id,
                    'book_title' => $item->book->title,
                    'book_author' => $item->book->author,
                    'price' => $item->book->display_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->book->display_price * $item->quantity,
                ]);

                $item->book->decrement('stock', $item->quantity);
            }

            // 4. CLEAR CART
            $cart->items()->delete();

            return $order;
        });
    }
}