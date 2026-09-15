<?php
// app/Models/Book.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'title', 'slug', 'author', 'publisher', 'isbn', 'year',
        'pages', 'language', 'description', 'price', 'discount_price',
        'stock', 'weight', 'is_active', 'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($b) {
            if (empty($b->slug)) {
                $base = Str::slug($b->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) $slug = $base . '-' . $i++;
                $b->slug = $slug;
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function category() { return $this->belongsTo(Category::class); }
    public function images() { return $this->hasMany(BookImage::class)->orderBy('sort_order'); }
    public function primaryImage() { return $this->hasOne(BookImage::class)->where('is_primary', true); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function cartItems() { return $this->hasMany(CartItem::class); }
    public function wishlistedBy() { return $this->hasMany(Wishlist::class); }

    // ==================== ACCESSORS ====================

    public function getDisplayPriceAttribute(): float
    {
        if ($this->discount_price !== null && $this->discount_price < $this->price) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price !== null
            && $this->discount_price > 0
            && $this->discount_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->has_discount) return 0;
        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function getImageUrlAttribute(): string
    {
        $image = $this->primaryImage ?? $this->images->first();
        return $image ? $image->image_url : asset('images/no-book-cover.png');
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    public function getStockLabelAttribute(): string
    {
        if ($this->stock <= 0) return 'Habis';
        if ($this->stock <= 5) return 'Sisa ' . $this->stock;
        return 'Tersedia';
    }

    // ==================== SCOPES ====================

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeInStock($q) { return $q->where('stock', '>', 0); }
    public function scopeAvailable($q) { return $q->active()->inStock(); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    public function scopeSearch($q, string $kw)
    {
        return $q->where(function ($query) use ($kw) {
            $query->where('title', 'like', "%{$kw}%")
                  ->orWhere('author', 'like', "%{$kw}%")
                  ->orWhere('publisher', 'like', "%{$kw}%")
                  ->orWhere('isbn', 'like', "%{$kw}%");
        });
    }

    public function scopeByCategory($q, string $slug)
    {
        return $q->whereHas('category', fn($query) => $query->where('slug', $slug));
    }

    public function scopeOnSale($q)
    {
        return $q->whereNotNull('discount_price')->whereColumn('discount_price', '<', 'price');
    }

    public function scopePriceRange($q, ?float $min, ?float $max)
    {
        return $q->when($min, fn($query, $v) => $query->where('price', '>=', $v))
                 ->when($max, fn($query, $v) => $query->where('price', '<=', $v));
    }

    public function scopeSortBy($q, ?string $sort)
    {
        return match ($sort) {
            'price_asc' => $q->orderBy('price', 'asc'),
            'price_desc' => $q->orderBy('price', 'desc'),
            'title_asc' => $q->orderBy('title', 'asc'),
            'title_desc' => $q->orderBy('title', 'desc'),
            'oldest' => $q->oldest(),
            default => $q->latest(),
        };
    }

    // ==================== HELPERS ====================

    public function decrementStock(int $qty): bool
    {
        if ($this->stock < $qty) return false;
        $this->decrement('stock', $qty);
        return true;
    }
}