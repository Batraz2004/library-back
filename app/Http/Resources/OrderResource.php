<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,   
            'status' => $this->status,   
            'address' => $this->address,  
            'full_price' => $this->full_price,   
            'phone' => $this->phone,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),  
        ];
    }
}
