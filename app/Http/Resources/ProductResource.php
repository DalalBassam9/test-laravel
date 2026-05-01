<?php

namespace App\Http\Resources;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Resources\Lookups\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    { {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'type' => $this->type,
                'order' => $this->order,
                'is_featured' => $this->is_featured,
                'description' => $this->description,
                'max_quantity' => $this->max_quantity,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'categories' => CategoryResource::collection($this->whenLoaded('categories')),
                'main_image' => $this->whenLoaded('images')
                    ? (($main = $this->images->firstWhere('is_main', true)) && $main->path
                        ? route('private.image', ['path' => $main->path])
                        : null)
                    : null,
                'images' => ProductImageResource::collection($this->whenLoaded('images')),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
    }
}
