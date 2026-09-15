<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'midtrans_transaction_id', 'midtrans_order_id',
        'payment_type', 'status', 'gross_amount', 'snap_token',
        'payment_url', 'expired_at', 'paid_at', 'raw_response',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function order() { return $this->belongsTo(Order::class); }
}