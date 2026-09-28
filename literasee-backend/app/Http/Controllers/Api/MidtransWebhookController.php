<?php
// app/Http/Controllers/Api/MidtransWebhookController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

    /**
     * POST /api/midtrans/webhook
     * Endpoint ini TIDAK pakai auth (Midtrans yang akan hit).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('[Midtrans Webhook]', $payload);

        // 1. Validasi payload wajib
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'] as $key) {
            if (empty($payload[$key])) {
                return response()->json(['message' => "Field '$key' tidak ada."], 400);
            }
        }

        // 2. Verifikasi signature (WAJIB, biar tidak bisa dipalsukan)
        if (! $this->midtrans->verifySignature($payload)) {
            Log::warning('[Midtrans Webhook] Signature tidak valid', $payload);
            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        // 3. Cari order
        $order = Order::where('order_number', $payload['order_id'])->first();

        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        $transactionStatus = $payload['transaction_status'];
        $fraudStatus       = $payload['fraud_status'] ?? null;

        // 4. Mapping status Midtrans → status internal
        //    settlement/capture = PAID, expire/cancel/deny = FAILED, pending = PENDING
        try {
            DB::transaction(function () use ($order, $transactionStatus, $fraudStatus, $payload) {

                if ($transactionStatus === 'capture') {
                    // Kartu kredit: cek fraud
                    if ($fraudStatus === 'challenge') {
                        $order->update(['payment_status' => 'challenge']);
                    } elseif ($fraudStatus === 'accept') {
                        $order->update([
                            'payment_status' => 'paid',
                            'status'         => 'processing', // 🔥 pending → processing
                            'paid_at'        => now(),
                        ]);
                    }
                } elseif ($transactionStatus === 'settlement') {
                    // Pembayaran sukses (transfer/QRIS/e-wallet)
                    $order->update([
                        'payment_status' => 'paid',
                        'status'         => 'processing', // 🔥 pending → processing
                        'paid_at'        => now(),
                    ]);
                } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    $order->update([
                        'payment_status' => 'failed',
                        'status'         => 'cancelled',
                    ]);
                } elseif ($transactionStatus === 'pending') {
                    // Tetap pending, jangan ubah apa-apa
                    $order->update(['payment_status' => 'pending']);
                }

                // Simpan raw payload untuk audit
                $order->update([
                    'midtrans_payload'   => json_encode($payload),
                    'midtrans_txn_id'    => $payload['transaction_id'] ?? null,
                    'midtrans_payment'   => $payload['payment_type'] ?? null,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('[Midtrans Webhook] DB error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal update order.'], 500);
        }

        // 5. Midtrans hanya butuh HTTP 200 OK
        return response()->json(['message' => 'OK']);
    }
}