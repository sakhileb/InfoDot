<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepResource extends JsonResource
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
            'solution_heading' => $this->solution_heading,
            'solution_body' => $this->solution_body,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'solution' => new SolutionResource($this->whenLoaded('solution')),
        ];
    }
}
