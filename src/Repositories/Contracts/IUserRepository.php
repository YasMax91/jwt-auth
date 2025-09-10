<?php

namespace RaDevs\JwtAuth\Repositories\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface IUserRepository
{
    public function create(array $data);
    public function getActivatedUserByField(string $field, string $value);
    public function showAuthenticated(): ?Authenticatable;
    public function update($user, array $data);
}