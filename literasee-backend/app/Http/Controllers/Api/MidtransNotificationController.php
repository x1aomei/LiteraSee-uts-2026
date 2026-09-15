<?php
// app/Http/Controllers/Api/MidtransNotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Notification', $payload);

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        if (!$orderId || !$transactionStatus || !$signatureKey) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // ============================================
        // VALIDASI SIGNATURE (KRITIS!)
        // ============================================
        $serverKey = config('midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans: Invalid signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_number', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // IDEMPOTENCY
        if (in_array($order->status, ['processing', 'shipped', 'delivered', 'cancelled'])) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        // Update payment record
        $payment = $order->payment ?: new Payment([
            'order_id' => $order->id,
            'gross_amount' => $order->total_amount,
        ]);

        $payment->fill([
            'midtrans_transaction_id' => $transactionId,
            'midtrans_order_id' => $orderId,
            'payment_type' => $paymentType,
            'raw_response' => $payload,
        ])->save();

        match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge'
                ? $this->handlePending($order, $payment)
                : $this->handleSuccess($order, $payment),
            'settlement' => $this->handleSuccess($order, $payment),
            'pending' => $this->handlePending($order, $payment),
            'deny', 'expire', 'cancel' => $this->handleFailed($order, $payment, $transactionStatus),
            'refund', 'partial_refund' => $this->handleRefund($order, $payment),
            default => Log::info('Unknown status', ['status' => $transactionStatus]),
        };

        return response()->json(['message' => 'OK'], 200);
    }

    protected function handleSuccess(Order $order, Payment $payment): void
    {
        Log::info("Payment SUCCESS: {$order->order_number}");

        $order->update(['status' => 'processing', 'payment_status' => 'paid']);
        $payment->update(['status' => 'success', 'paid_at' => now()]);

        // Trigger email (Hari 9)
        event(new \App\Events\OrderPaidEvent($order));
    }

    protected function handlePending(Order $order, Payment $payment): void
    {
        $order->update(['payment_status' => 'unpaid']);
        $payment->update(['status' => 'pending']);
    }

    protected function handleFailed(Order $order, Payment $payment, string $reason): void
    {
        Log::info("Payment FAILED: {$order->order_number} ({$reason})");

        $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
        $payment->update(['status' => 'failed']);

        // RESTOCK
        foreach ($order->items as $item) {
            $item->book?->increment('stock', $item->quantity);
        }
    }

    protected function handleRefund(Order $order, Payment $payment): void
    {
        $payment->update(['status' => 'refunded']);
        $order->update(['payment_status' => 'refunded']);
    }
}