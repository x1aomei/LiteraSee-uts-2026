<?php
// app/Http/Controllers/Api/CatalogController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\CategoryResource;
use App\Models\Book;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/home
     *
     * Cache 30 menit, tapi cache disimpan sebagai PLAIN ARRAY
     * (bukan Eloquent Collection) supaya tidak corrupt.
     */
    public function home()
    {
        $data = Cache::remember('homepage_data_v3', 1800, function () {
            $categories = Category::active()
                ->withCount(['activeBooks'])
                ->having('active_books_count', '>', 0)
                ->orderBy('name')
                ->take(6)
                ->get();

            $featuredBooks = Book::with(['category:id,name', 'primaryImage'])
                ->available()
                ->featured()
                ->whereHas('primaryImage')
                ->latest()
                ->take(8)
                ->get();

            $latestBooks = Book::with(['category:id,name', 'primaryImage'])
                ->available()
                ->whereHas('primaryImage')
                ->latest()
                ->take(8)
                ->get();

            $onSaleBooks = Book::with(['category:id,name', 'primaryImage'])
                ->available()
                ->onSale()
                ->whereHas('primaryImage')
                ->orderByRaw('(price - discount_price) / price DESC')
                ->take(4)
                ->get();

            // ⚠️ ->resolve() ubah Resource jadi plain array — INI KUNCINYA
            return [
                'categories'     => CategoryResource::collection($categories)->resolve(),
                'featured_books' => BookResource::collection($featuredBooks)->resolve(),
                'latest_books'   => BookResource::collection($latestBooks)->resolve(),
                'on_sale_books'  => BookResource::collection($onSaleBooks)->resolve(),
            ];
        });

        return $this->successResponse($data);
    }

    /**
     * GET /api/catalog
     */
    public function index(Request $request)
    {
        $query = Book::with(['category:id,name,slug', 'primaryImage'])->available();

        if ($request->filled('q')) $query->search($request->q);
        if ($request->filled('category')) $query->byCategory($request->category);

        $query->priceRange(
            $request->filled('min_price') ? (float) $request->min_price : null,
            $request->filled('max_price') ? (float) $request->max_price : null,
        );

        if ($request->filled('language')) $query->where('language', $request->language);
        if ($request->filled('author')) $query->where('author', 'like', '%' . $request->author . '%');
        if ($request->boolean('on_sale')) $query->onSale();
        if ($request->boolean('featured')) $query->featured();

        $query->sortBy($request->get('sort', 'newest'));

        $books = $query->paginate($request->per_page ?? 12);

        return $this->successResponse([
            'books' => BookResource::collection($books)->response()->getData(true),
            'filters' => [
                'categories'  => CategoryResource::collection($this->getCategories()),
                'price_range' => $this->getPriceRange(),
                'languages'   => $this->getLanguages(),
            ],
        ]);
    }

    /**
     * GET /api/books/{slug}
     */
    public function show(string $slug)
    {
        $book = Book::available()
            ->with(['category', 'images' => fn($q) => $q->orderBy('sort_order')])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedBooks = Book::with(['category:id,name', 'primaryImage'])
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->available()
            ->inRandomOrder()
            ->take(4)
            ->get();

        $sameAuthorBooks = Book::with(['category:id,name', 'primaryImage'])
            ->where('author', $book->author)
            ->where('id', '!=', $book->id)
            ->available()
            ->take(4)
            ->get();

        return $this->successResponse([
            'book'              => new BookResource($book),
            'related_books'     => BookResource::collection($relatedBooks),
            'same_author_books' => BookResource::collection($sameAuthorBooks),
        ]);
    }

    /**
     * GET /api/categories
     */
    public function categories()
    {
        return $this->successResponse(
            CategoryResource::collection($this->getCategories())
        );
    }

    // ============================================
    // HELPER METHODS — SEMUA PAKAI CACHE + RESOLVE
    // ============================================

    protected function getCategories()
    {
        return Category::active()
            ->withCount(['activeBooks'])
            ->having('active_books_count', '>', 0)
            ->orderBy('name')
            ->get();
    }

    protected function getPriceRange()
    {
        return Cache::remember('book_price_range_v2', 3600, function () {
            $r = Book::available()
                ->selectRaw('MIN(price) as min, MAX(price) as max')
                ->first();

            return [
                'min' => (float) ($r->min ?? 0),
                'max' => (float) ($r->max ?? 0),
            ];
        });
    }

    protected function getLanguages()
    {
        return Cache::remember('book_languages_v2', 3600, function () {
            return Book::available()
                ->distinct()
                ->pluck('language')
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        });
    }
}