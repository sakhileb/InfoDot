<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolutionResource extends JsonResource
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
            'solution_title' => $this->solution_title,
            'solution_description' => $this->solution_description,
            'tags' => $this->tags,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
            'steps' => $this->steps,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'solution_steps' => StepResource::collection($this->whenLoaded('steps')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            
            // Counts
            'steps_count' => $this->when(isset($this->steps_count), $this->steps_count),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count),
            'dislikes_count' => $this->when(isset($this->dislikes_count), $this->dislikes_count),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            
            // User interaction status
            'user_liked' => $this->when(
                $request->user(),
                fn() => $this->likes()->where('user_id', $request->user()?->id)->where('like', true)->exists()
            ),
            'user_disliked' => $this->when(
                $request->user(),
                fn() => $this->likes()->where('user_id', $request->user()?->id)->where('like', false)->exists()
            ),
        ];
    }
}
