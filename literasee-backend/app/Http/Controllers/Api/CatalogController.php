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
     */
    public function home()
    {
        $data = Cache::remember('homepage_data', 1800, function () {
            return [
                'categories' => Category::active()
                    ->withCount(['activeBooks'])
                    ->having('active_books_count', '>', 0)
                    ->orderBy('name')
                    ->take(6)
                    ->get(),
                'featured_books' => Book::with(['category:id,name', 'primaryImage'])
                    ->available()->featured()->latest()->take(8)->get(),
                'latest_books' => Book::with(['category:id,name', 'primaryImage'])
                    ->available()->latest()->take(8)->get(),
                'on_sale_books' => Book::with(['category:id,name', 'primaryImage'])
                    ->available()->onSale()
                    ->orderByRaw('(price - discount_price) / price DESC')
                    ->take(4)->get(),
            ];
        });

        return $this->successResponse([
            'categories' => CategoryResource::collection($data['categories']),
            'featured_books' => BookResource::collection($data['featured_books']),
            'latest_books' => BookResource::collection($data['latest_books']),
            'on_sale_books' => BookResource::collection($data['on_sale_books']),
        ]);
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
                'categories' => CategoryResource::collection($this->getCategories()),
                'price_range' => $this->getPriceRange(),
                'languages' => $this->getLanguages(),
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
            ->take(4)->get();

        $sameAuthorBooks = Book::with(['category:id,name', 'primaryImage'])
            ->where('author', $book->author)
            ->where('id', '!=', $book->id)
            ->available()->take(4)->get();

        return $this->successResponse([
            'book' => new BookResource($book),
            'related_books' => BookResource::collection($relatedBooks),
            'same_author_books' => BookResource::collection($sameAuthorBooks),
        ]);
    }

    /**
     * GET /api/categories
     */
    public function categories()
    {
        return $this->successResponse(CategoryResource::collection($this->getCategories()));
    }

    protected function getCategories()
    {
        return Cache::remember('global_categories', 3600, function () {
            return Category::active()
                ->withCount(['activeBooks'])
                ->having('active_books_count', '>', 0)
                ->orderBy('name')->get();
        });
    }

    protected function getPriceRange()
    {
        return Cache::remember('book_price_range', 3600, function () {
            $r = Book::available()->selectRaw('MIN(price) as min, MAX(price) as max')->first();
            return ['min' => (float) ($r->min ?? 0), 'max' => (float) ($r->max ?? 0)];
        });
    }

    protected function getLanguages()
    {
        return Cache::remember('book_languages', 3600, function () {
            return Book::available()->distinct()->pluck('language')->filter()->sort()->values()->toArray();
        });
    }
}