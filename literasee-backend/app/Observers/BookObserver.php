<?php
// app/Observers/BookObserver.php

namespace App\Observers;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookObserver
{
    public function created(Book $book): void
    {
        $this->clearCache($book);
        Log::info("📚 Book created: {$book->title}");
    }

    public function updated(Book $book): void
    {
        $this->clearCache($book);
    }

    public function deleted(Book $book): void
    {
        $this->clearCache($book);
        Log::warning("🗑️ Book deleted: {$book->title}");
    }

    protected function clearCache(Book $book): void
    {
        Cache::forget('featured_books');
        Cache::forget('latest_books');
        Cache::forget("book_{$book->id}");
        Cache::forget('homepage_data');
    }
}