<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        if ($order->items->isEmpty()) throw new Exception('Order tidak memiliki item.');

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->shipping_phone ?? '',
                'billing_address' => [
                    'first_name' => $order->shipping_name,
                    'phone' => $order->shipping_phone,
                    'address' => $order->shipping_address,
                ],
                'shipping_address' => [
                    'first_name' => $order->shipping_name,
                    'phone' => $order->shipping_phone,
                    'address' => $order->shipping_address,
                ],
            ],
            'item_details' => $order->items->map(fn($item) => [
                'id' => (string) $item->book_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->book_title, 0, 50),
            ])->toArray(),
        ];

        try {
            return Snap::getSnapToken($params);
        } catch (Exception $e) {
            logger()->error('Midtrans Snap Error', [
                'order_id' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }
    }

    public function checkStatus(string $orderId)
    {
        try {
            return Transaction::status($orderId);
        } catch (Exception $e) {
            throw new Exception('Gagal cek status: ' . $e->getMessage());
        }
    }

    public function cancelTransaction(string $orderId)
    {
        try {
            return Transaction::cancel($orderId);
        } catch (Exception $e) {
            throw new Exception('Gagal cancel transaksi: ' . $e->getMessage());
        }
    }
}