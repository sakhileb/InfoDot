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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_photo_url' => $this->profile_photo_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Conditional relationships
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'solutions' => SolutionResource::collection($this->whenLoaded('solutions')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            
            // Counts
            'questions_count' => $this->when(isset($this->questions_count), $this->questions_count),
            'solutions_count' => $this->when(isset($this->solutions_count), $this->solutions_count),
            'answers_count' => $this->when(isset($this->answers_count), $this->answers_count),
            'followers_count' => $this->when(isset($this->followers_count), $this->followers_count),
            'following_count' => $this->when(isset($this->following_count), $this->following_count),
        ];
    }
}
