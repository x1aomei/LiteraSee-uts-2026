<?php
// app/Observers/CategoryObserver.php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function created(Category $c): void
    {
        Cache::forget('global_categories');
        Cache::forget('homepage_data');
    }

    public function updated(Category $c): void
    {
        Cache::forget('global_categories');
        Cache::forget('homepage_data');
    }

    public function deleted(Category $c): void
    {
        Cache::forget('global_categories');
        Cache::forget('homepage_data');
    }
}