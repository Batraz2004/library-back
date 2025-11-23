<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,   
            'cart_id' => $this->cart_id,   
            'book_id' => $this->book_id,   
            'quantity' => $this->quantity,   
            'is_checked' => $this->is_checked, 
            'price' => $this?->book?->price * $this->quantity,
            'book' => BookResource::make($this->whenLoaded('book')),
        ];
    }
}
