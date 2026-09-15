<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Models\Book;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Dapatkan user dari Sanctum token (kalau ada).
     */
    private function currentUser()
    {
        return auth('sanctum')->user();
    }

    /**
     * Dapatkan atau buat cart untuk user/guest saat ini.
     */
    public function getCart(): Cart
    {
        return DB::transaction(function () {
            $user = $this->currentUser();

            if ($user) {
                return Cart::firstOrCreate(['user_id' => $user->id]);
            }

            return Cart::firstOrCreate(['session_id' => Session::getId()]);
        });
    }

    public function getCartWithItems(): Cart
    {
        $cart = $this->getCart();
        $cart->load(['items.book' => fn($q) => $q->with('primaryImage')]);
        return $cart;
    }

    public function addBook(Book $book, int $quantity = 1): CartItem
    {
        if (!$book->is_active) throw new CartException('Buku tidak tersedia.');
        if ($book->stock <= 0) throw new InsufficientStockException('Buku sedang habis.');
        if ($quantity < 1) throw new CartException('Jumlah minimal 1.');

        return DB::transaction(function () use ($book, $quantity) {
            $cart = $this->getCart();
            $existing = $cart->items()->where('book_id', $book->id)->lockForUpdate()->first();

            if ($existing) {
                $newQty = $existing->quantity + $quantity;
                if ($newQty > $book->stock) {
                    throw new InsufficientStockException("Stok tidak mencukupi. Tersisa: {$book->stock}");
                }
                $existing->update(['quantity' => $newQty]);
                $item = $existing;
            } else {
                if ($quantity > $book->stock) {
                    throw new InsufficientStockException("Stok hanya {$book->stock}.");
                }
                $item = $cart->items()->create(['book_id' => $book->id, 'quantity' => $quantity]);
            }

            $cart->touch();
            return $item;
        });
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        DB::transaction(function () use ($itemId, $quantity) {
            $item = CartItem::lockForUpdate()->findOrFail($itemId);
            $this->verifyOwnership($item->cart);

            if ($quantity <= 0) { $item->delete(); return; }

            $book = $item->book()->lockForUpdate()->first();
            if ($quantity > $book->stock) {
                throw new InsufficientStockException("Stok tidak mencukupi. Tersisa: {$book->stock}");
            }
            $item->update(['quantity' => $quantity]);
            $item->cart->touch();
        });
    }

    public function removeItem(int $itemId): void
    {
        $item = CartItem::findOrFail($itemId);
        $this->verifyOwnership($item->cart);
        $item->delete();
        $item->cart->touch();
    }

    public function clearCart(): void
    {
        $this->getCart()->items()->delete();
    }

    public function mergeCartOnLogin(): void
    {
        $user = $this->currentUser();
        if (!$user) return;

        $sessionId = Session::getId();
        $guestCart = Cart::where('session_id', $sessionId)->with('items')->first();
        if (!$guestCart || $guestCart->items->isEmpty()) return;

        DB::transaction(function () use ($guestCart, $user) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $item) {
                $existing = $userCart->items()->where('book_id', $item->book_id)->first();
                if ($existing) {
                    $maxStock = $item->book->stock;
                    $existing->update(['quantity' => min($existing->quantity + $item->quantity, $maxStock)]);
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }
            $guestCart->delete();
        });
    }

    public function getSummary(): array
    {
        $cart = $this->getCartWithItems();
        $subtotal = $cart->items->sum(fn($i) => $i->book->display_price * $i->quantity);

        return [
            'item_count' => $cart->items->sum('quantity'),
            'unique_count' => $cart->items->count(),
            'subtotal' => (float) $subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
        ];
    }

    private function verifyOwnership(Cart $cart): void
    {
        if ($cart->id !== $this->getCart()->id) {
            throw new CartException('Akses ditolak.');
        }
    }

    public function cleanExpiredGuestCarts(int $days = 7): int
    {
        return Cart::whereNull('user_id')
            ->where('updated_at', '<', now()->subDays($days))
            ->delete();
    }
}
