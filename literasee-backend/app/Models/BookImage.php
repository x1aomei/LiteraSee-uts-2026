<?php
// app/Models/BookImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BookImage extends Model
{
    protected $fillable = ['book_id', 'image_path', 'is_primary', 'sort_order'];
    protected $casts = ['is_primary' => 'boolean'];

    public function book() { return $this->belongsTo(Book::class); }

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http')) return $this->image_path;
        return asset('storage/' . $this->image_path);
    }

    public function makePrimary(): void
    {
        $this->book->images()->where('id', '!=', $this->id)->update(['is_primary' => false]);
        $this->update(['is_primary' => true]);
    }
}