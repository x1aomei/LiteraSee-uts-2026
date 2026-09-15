<?php
// app/Http/Resources/OrderResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'payment_status' => $this->payment_status,
            'total_amount' => (float) $this->total_amount,
            'formatted_total' => 'Rp ' . number_format($this->total_amount, 0, ',', '.'),
            'shipping_cost' => (float) $this->shipping_cost,
            'shipping_name' => $this->shipping_name,
            'shipping_phone' => $this->shipping_phone,
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'snap_token' => $this->snap_token,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user' => new UserResource($this->whenLoaded('user')),
            'payment' => $this->whenLoaded('payment'),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}