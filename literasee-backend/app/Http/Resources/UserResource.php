<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatar_url' => $this->avatar_url,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_admin' => $this->isAdmin(),
            'email_verified' => !is_null($this->email_verified_at),
            'orders_count' => $this->when(isset($this->orders_count), $this->orders_count),
            'total_spent' => $this->when(isset($this->total_spent), (float) $this->total_spent),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}