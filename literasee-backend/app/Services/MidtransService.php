<?php
// app/Services/MidtransService.php

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
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        if ($order->items->isEmpty()) {
            throw new Exception('Order tidak memiliki item.');
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email'      => $order->user->email,
                'phone'      => $order->shipping_phone ?? '',
                'billing_address' => [
                    'first_name' => $order->shipping_name,
                    'phone'      => $order->shipping_phone,
                    'address'    => $order->shipping_address,
                ],
                'shipping_address' => [
                    'first_name' => $order->shipping_name,
                    'phone'      => $order->shipping_phone,
                    'address'    => $order->shipping_address,
                ],
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id'       => (string) $item->book_id,
                'price'    => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name'     => substr($item->book_title, 0, 50),
            ])->toArray(),
        ];

        try {
            return Snap::getSnapToken($params);
        } catch (Exception $e) {
            logger()->error('Midtrans Snap Error', [
                'order_id' => $order->order_number,
                'error'    => $e->getMessage(),
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

    /**
     * Verifikasi signature_key dari notifikasi/webhook Midtrans.
     *
     * Rumus resmi Midtrans:
     *   signature_key = sha512(order_id + status_code + gross_amount + server_key)
     *
     * @see https://docs.midtrans.com/docs/https-notification-webhooks
     */
    public function verifySignature(array $payload): bool
    {
        if (empty($payload['signature_key'])) {
            return false;
        }

        foreach (['order_id', 'status_code', 'gross_amount'] as $key) {
            if (! isset($payload[$key])) {
                return false;
            }
        }

        $expected = hash(
            'sha512',
            $payload['order_id']
            . $payload['status_code']
            . $payload['gross_amount']
            . config('midtrans.server_key')
        );

        // hash_equals() → timing-safe comparison, cegah timing attack
        return hash_equals($expected, $payload['signature_key']);
    }

    /**
     * Helper opsional: mapping transaction_status Midtrans → status internal.
     * Berguna kalau mau dipakai juga oleh fallback checkStatus().
     */
    public function mapTransactionStatus(string $transactionStatus, ?string $fraudStatus = null): array
    {
        return match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'accept'  => ['payment_status' => 'paid',    'status' => 'processing'],
            $transactionStatus === 'capture' && $fraudStatus === 'challenge' => ['payment_status' => 'challenge','status' => 'pending'],
            $transactionStatus === 'settlement'                            => ['payment_status' => 'paid',    'status' => 'processing'],
            $transactionStatus === 'pending'                               => ['payment_status' => 'pending', 'status' => 'pending'],
            in_array($transactionStatus, ['deny', 'cancel', 'expire'], true) => ['payment_status' => 'failed',  'status' => 'cancelled'],
            $transactionStatus === 'refund'                                => ['payment_status' => 'refunded','status' => 'cancelled'],
            default                                                        => ['payment_status' => 'pending', 'status' => 'pending'],
        };
    }
}