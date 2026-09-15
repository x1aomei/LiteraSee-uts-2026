<?php
// app/Http/Controllers/Api/Admin/BookController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\BookImage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $books = Book::with(['category:id,name,slug', 'primaryImage'])
            ->when($request->q, fn($q, $kw) => $q->search($kw))
            ->when($request->category, fn($q, $cat) => $q->byCategory($cat))
            ->when($request->status === 'low_stock', fn($q) => $q->where('stock', '<=', 5)->where('stock', '>', 0))
            ->when($request->status === 'out_of_stock', fn($q) => $q->where('stock', 0))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse(BookResource::collection($books)->response()->getData(true));
    }

    public function store(BookRequest $request)
    {
        try {
            $book = DB::transaction(function () use ($request) {
                $book = Book::create($request->validated());

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $i => $file) {
                        $path = $file->store('books', 'public');
                        $book->images()->create([
                            'image_path' => $path,
                            'is_primary' => $i === 0,
                            'sort_order' => $i,
                        ]);
                    }
                }
                return $book;
            });

            return $this->createdResponse(
                new BookResource($book->load('category', 'images')),
                'Buku berhasil ditambahkan!'
            );
        } catch (\Exception $e) {
            logger()->error('Book create: ' . $e->getMessage());
            return $this->errorResponse('Gagal menambahkan buku.', 500);
        }
    }

    public function show(Book $book)
    {
        $book->load(['category', 'images']);
        return $this->successResponse(new BookResource($book));
    }

    public function update(BookRequest $request, Book $book)
    {
        try {
            DB::transaction(function () use ($request, $book) {
                $book->update($request->validated());

                if ($request->hasFile('images')) {
                    $startOrder = $book->images()->max('sort_order') + 1;
                    foreach ($request->file('images') as $i => $file) {
                        $path = $file->store('books', 'public');
                        $book->images()->create([
                            'image_path' => $path,
                            'is_primary' => false,
                            'sort_order' => $startOrder + $i,
                        ]);
                    }
                }
            });

            return $this->successResponse(
                new BookResource($book->fresh(['category', 'images'])),
                'Buku berhasil diperbarui!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui buku.', 500);
        }
    }

    public function destroy(Book $book)
    {
        if ($book->orderItems()->exists()) {
            return $this->errorResponse('Buku tidak bisa dihapus karena sudah ada di pesanan.', 422);
        }
        $book->delete();
        return $this->noContentResponse('Buku berhasil dihapus.');
    }

    public function deleteImage(Book $book, BookImage $image)
    {
        if ($image->book_id !== $book->id) return $this->errorResponse('Akses ditolak.', 403);
        $image->delete();
        return $this->successResponse(null, 'Gambar dihapus.');
    }

    public function setPrimaryImage(Book $book, BookImage $image)
    {
        if ($image->book_id !== $book->id) return $this->errorResponse('Akses ditolak.', 403);
        $image->makePrimary();
        return $this->successResponse(null, 'Gambar utama diperbarui.');
    }
}