<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $parts = explode(' ', $this->name, 2);

        return [
            'firstName' => $parts[0] ?? '',
            'lastName'  => $parts[1] ?? '',
            'email'     => $this->email,
            'phone'     => $this->phone,
        ];
    }
}
