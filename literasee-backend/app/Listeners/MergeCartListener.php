<?php
// app/Listeners/MergeCartListener.php

namespace App\Listeners;

use App\Services\CartService;

class MergeCartListener
{
    public function handle(object $event): void
    {
        (new CartService())->mergeCartOnLogin();
    }
}