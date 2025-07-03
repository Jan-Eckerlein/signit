<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'own_user_id' => $this->own_user_id,
            'knows_user_id' => $this->knows_user_id,
            'knows_anonymous_users_id' => $this->knows_anonymous_users_id,
            'email' => $this->email,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'from_user' => new UserResource($this->whenLoaded('fromUser')),
            'knows_user' => new UserResource($this->whenLoaded('knowsUser')),
            'knows_anonymous_user' => new AnonymousUserResource($this->whenLoaded('knowsAnonymousUser')),
        ];
    }
} 