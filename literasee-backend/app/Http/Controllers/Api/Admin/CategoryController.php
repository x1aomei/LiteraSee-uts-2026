<?php
// app/Http/Controllers/Api/Admin/CategoryController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $categories = Category::withCount('books')->latest()->paginate(15);
        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);
        return $this->createdResponse(new CategoryResource($category), 'Kategori berhasil ditambahkan!');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);
        return $this->successResponse(new CategoryResource($category), 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return $this->errorResponse('Kategori masih memiliki buku.', 422);
        }

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return $this->noContentResponse('Kategori berhasil dihapus.');
    }
}