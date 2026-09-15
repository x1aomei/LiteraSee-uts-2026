<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CartException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Book;
use App\Services\CartService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(protected CartService $cartService) {}

    public function session()
    {
        if (!auth()->check()) {
            return $this->successResponse(['session_id' => Session::getId(), 'is_guest' => true]);
        }
        return $this->successResponse(['session_id' => null, 'is_guest' => false]);
    }

    public function index()
    {
        $cart = $this->cartService->getCartWithItems();
        return $this->successResponse(new CartResource($cart));
    }

    public function summary()
    {
        return $this->successResponse($this->cartService->getSummary());
    }

    public function add(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        try {
            $book = Book::findOrFail($request->book_id);
            $this->cartService->addBook($book, $request->quantity);
            return $this->successResponse($this->cartService->getSummary(), 'Buku ditambahkan!');
        } catch (CartException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(Request $request, int $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:0|max:99']);
        try {
            $this->cartService->updateQuantity($itemId, $request->quantity);
            return $this->successResponse($this->cartService->getSummary(), 'Keranjang diperbarui.');
        } catch (CartException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function remove(int $itemId)
    {
        try {
            $this->cartService->removeItem($itemId);
            return $this->successResponse($this->cartService->getSummary(), 'Buku dihapus.');
        } catch (CartException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
