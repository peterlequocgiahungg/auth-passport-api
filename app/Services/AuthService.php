<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Laravel\Passport\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PassportAccessTokenController $passport,
    ) {}

    /**
     * @param array{name: string, email: string, password: string} $attributes
     */
    public function register(array $attributes): User
    {
        return $this->users->create($attributes);
    }

    public function login(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): Response {
        return $this->issueToken($request, $response, 'password');
    }

    public function refresh(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): Response {
        return $this->issueToken($request, $response, 'refresh_token');
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

    private function issueToken(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $grantType,
    ): Response {
        $body = $request->getParsedBody();
        $body = is_array($body) ? $body : [];

        return $this->passport->issueToken(
            $request->withParsedBody([...$body, 'grant_type' => $grantType]),
            $response,
        );
    }
}
