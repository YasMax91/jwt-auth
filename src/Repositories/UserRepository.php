<?php

namespace RaDevs\JwtAuth\Repositories;


use Illuminate\Contracts\Auth\Authenticatable;
use RaDevs\JwtAuth\Repositories\Contracts\IUserRepository;


class UserRepository implements IUserRepository
{
    public function create(array $data)
    {
        $userModel = config('ra-jwt-auth.classes.user_model');
        $user = $userModel::query()->create($data);

        return $user;
    }


    public function getActivatedUserByField(string $field, string $value)
    {
        $userModel = config('ra-jwt-auth.classes.user_model');
        return $userModel::query()->where($field, $value)->first();
    }


    public function showAuthenticated(): ?Authenticatable
    {
        return auth()->user();
    }


    public function update($user, array $data)
    {
        $user->update($data);
        return $user->fresh();
    }
}