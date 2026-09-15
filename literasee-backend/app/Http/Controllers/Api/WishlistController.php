<?php
// app/Http/Controllers/Api/WishlistController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Traits\ApiResponse;

class WishlistController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/wishlist
     */
    public function index()
    {
        $books = auth()->user()->wishlists()
            ->with(['category:id,name', 'primaryImage'])
            ->latest('wishlists.created_at')
            ->paginate(request('per_page', 12));

        return $this->successResponse([
            'books' => BookResource::collection($books)->response()->getData(true),
            'count' => auth()->user()->wishlists()->count(),
        ]);
    }

    /**
     * POST /api/wishlist/toggle/{book}
     */
    public function toggle(Book $book)
    {
        $user = auth()->user();
        $wasWishlisted = $user->hasInWishlist($book);

        if ($wasWishlisted) {
            $user->wishlists()->detach($book->id);
            $added = false;
            $message = 'Buku dihapus dari wishlist.';
        } else {
            $user->wishlists()->attach($book->id);
            $added = true;
            $message = 'Buku ditambahkan ke wishlist!';
        }

        return $this->successResponse([
            'added' => $added,
            'book_id' => $book->id,
            'count' => $user->wishlists()->count(),
        ], $message);
    }

    /**
     * DELETE /api/wishlist/{book}
     */
    public function destroy(Book $book)
    {
        auth()->user()->wishlists()->detach($book->id);
        return $this->successResponse(
            ['count' => auth()->user()->wishlists()->count()],
            'Buku dihapus dari wishlist.'
        );
    }
}