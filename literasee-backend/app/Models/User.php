<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'phone', 'address',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function cart() { return $this->hasOne(Cart::class); }

    public function wishlists()
    {
        return $this->belongsToMany(Book::class, 'wishlists')->withTimestamps();
    }

    public function orders() { return $this->hasMany(Order::class); }

    // ==================== HELPERS ====================

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    public function hasInWishlist(Book $book): bool
    {
        return $this->wishlists()->where('book_id', $book->id)->exists();
    }

    // ==================== ACCESSORS ====================

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }
}