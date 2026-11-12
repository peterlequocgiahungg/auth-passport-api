<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Laravel\Passport\Token;

class AuthService
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    /**
     * @param array{name: string, email: string, password: string} $attributes
     */
    public function register(array $attributes): User
    {
        return $this->users->create($attributes);
    }

    public function logout(User $user): void
    {
        $token = $user->token();
        if (! $token instanceof Token) {
            return;
        }

        $token->revoke();
        $token->refreshToken?->revoke();
    }
}
