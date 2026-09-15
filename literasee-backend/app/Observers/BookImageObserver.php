<?php
// app/Observers/BookImageObserver.php

namespace App\Observers;

use App\Models\BookImage;
use Illuminate\Support\Facades\Storage;

class BookImageObserver
{
    public function deleting(BookImage $image): void
    {
        if ($image->image_path
            && !str_starts_with($image->image_path, 'http')
            && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
    }

    public function updated(BookImage $image): void
    {
        if ($image->isDirty('is_primary') && $image->is_primary) {
            BookImage::where('book_id', $image->book_id)
                ->where('id', '!=', $image->id)
                ->update(['is_primary' => false]);
        }
    }
}