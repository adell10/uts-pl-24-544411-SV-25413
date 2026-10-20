<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'area' => $this->area,
            'status' => $this->status,
            'price_level' => $this->price_level,
            'rating' => $this->rating,
            'work_friendly' => $this->work_friendly,
            'facilities' => $this->facilities ?? [],
            'favorite_menu' => $this->favorite_menu,
            'notes' => $this->notes,
            'last_visited_at' => $this->last_visited_at?->format('Y-m-d'),
            'visit_count' => $this->visit_count,
            'color' => $this->color,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}