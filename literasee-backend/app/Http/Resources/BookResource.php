<?php
// app/Http/Resources/BookResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'year' => $this->year,
            'pages' => $this->pages,
            'language' => $this->language,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'display_price' => $this->display_price,
            'formatted_price' => 'Rp ' . number_format($this->display_price, 0, ',', '.'),
            'has_discount' => $this->has_discount,
            'discount_percentage' => $this->discount_percentage,
            'stock' => $this->stock,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'stock_label' => $this->stock_label,
            'image_url' => $this->image_url,
            'images' => BookImageResource::collection($this->whenLoaded('images')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}