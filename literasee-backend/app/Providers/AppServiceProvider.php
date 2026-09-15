<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Events\OrderPaidEvent;
use App\Listeners\MergeCartListener;
use App\Listeners\SendOrderPaidEmail;
use App\Models\Book;
use App\Models\BookImage;
use App\Models\Category;
use App\Observers\BookImageObserver;
use App\Observers\BookObserver;
use App\Observers\CategoryObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Event listeners
        Event::listen(Login::class, MergeCartListener::class);
        //Event::listen(OrderPaidEvent::class, SendOrderPaidEmail::class);  // ← TAMBAHKAN

        // Model observers (aktifkan lagi kalau sudah bikin file observer-nya)
        Book::observe(BookObserver::class);
        Category::observe(CategoryObserver::class);
        BookImage::observe(BookImageObserver::class);
    }
}