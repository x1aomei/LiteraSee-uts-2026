<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($c) {
            if (empty($c->slug)) $c->slug = Str::slug($c->name);
        });
        static::updating(function ($c) {
            if ($c->isDirty('name')) $c->slug = Str::slug($c->name);
        });
    }

    public function books() { return $this->hasMany(Book::class); }
    public function activeBooks()
    {
        return $this->hasMany(Book::class)->where('is_active', true)->where('stock', '>', 0);
    }
    public function scopeActive($q) { return $q->where('is_active', true); }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/category-placeholder.png');
    }
}