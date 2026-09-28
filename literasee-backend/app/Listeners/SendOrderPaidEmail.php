<?php
// app/Listeners/SendOrderPaidEmail.php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Mail\OrderPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderPaidEmail implements ShouldQueue  // ← ini penting
{
    public $tries = 3;  // retry 3x kalau gagal
    
    public function handle(OrderPaidEvent $event): void
    {
        Mail::to($event->order->user->email)->send(new OrderPaid($event->order));
    }
}