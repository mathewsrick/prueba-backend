<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'tax_cost' => $this->tax_cost,
            'manufacturing_cost' => $this->manufacturing_cost,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'prices' => ProductPriceResource::collection($this->whenLoaded('prices')), // F4
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
