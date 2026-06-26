<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;

        return [
            'name'   => $user->name,
            'avatar' => $user->avatar(),
        ];
    }
}
