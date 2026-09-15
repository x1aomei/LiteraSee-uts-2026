<?php
// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(protected MidtransService $midtrans) {}

    /**
     * POST /api/payments/{order}/snap-token
     */
    public function getSnapToken(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        if ($order->payment_status === 'paid') {
            return $this->errorResponse('Pesanan sudah dibayar.', 400);
        }

        try {
            $snapToken = $this->midtrans->createSnapToken($order);
            $order->update(['snap_token' => $snapToken]);

            return $this->successResponse([
                'snap_token' => $snapToken,
                'client_key' => config('midtrans.client_key'),
                'snap_url' => config('midtrans.snap_url'),
            ], 'Snap token berhasil dibuat.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/payments/{order}/check-status
     * Manual cek status ke Midtrans (fallback kalau webhook telat).
     */
    public function checkStatus(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return $this->errorResponse('Akses ditolak.', 403);
        }

        try {
            $status = $this->midtrans->checkStatus($order->order_number);
            return $this->successResponse($status);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}