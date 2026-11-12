<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @param array{name: string, email: string, password: string} $attributes
     */
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }
}
