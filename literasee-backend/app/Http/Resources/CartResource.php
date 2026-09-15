<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal = $this->items->sum(fn($i) => $i->book->display_price * $i->quantity);

        return [
            'id' => $this->id,
            'item_count' => $this->items->sum('quantity'),
            'subtotal' => (float) $subtotal,
            'subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
            // ⭐ PAKAI ->resolve() biar jadi plain array, bukan nested wrapper
            'items' => CartItemResource::collection($this->items)->resolve(),
        ];
    }
}
