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
            'status' => $this->status,
            'total_price' => $this->total_price,
            'delivery_fee' => $this->delivery_fee,
            'notes' => $this->notes,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'address' => $this->whenLoaded('address', function () {
                return [
                    'city' => $this->address->city->name ?? null,
                    'phone' => $this->address->phone ?? null,
                    'name' => $this->address->user->name
                        ?? trim(($this->address->firstName ?? '') . ' ' . ($this->address->lastName ?? ''))
                        ?? null,
                    'address' => $this->address->address ?? null,
                ];
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
