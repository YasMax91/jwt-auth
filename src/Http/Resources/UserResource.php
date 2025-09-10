<?php

namespace RaDevs\JwtAuth\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** @mixin \Illuminate\Contracts\Auth\Authenticatable */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? null,
            'last_name' => $this->last_name ?? null,
            'email' => $this->email,
        ];
    }
}