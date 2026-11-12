<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RefreshTokenRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Tạo tài khoản thành công. Hãy đăng nhập để nhận token.',
            'user' => $user,
        ], 201);
    }

    public function login(
        LoginRequest $request,
        ServerRequestInterface $psrRequest,
        ResponseInterface $psrResponse,
        PassportAccessTokenController $passport,
    ): Response {
        return $this->issueToken($psrRequest, $psrResponse, $passport, 'password');
    }

    public function refresh(
        RefreshTokenRequest $request,
        ServerRequestInterface $psrRequest,
        ResponseInterface $psrResponse,
        PassportAccessTokenController $passport,
    ): Response {
        return $this->issueToken($psrRequest, $psrResponse, $passport, 'refresh_token');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Đăng xuất thành công.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    private function issueToken(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PassportAccessTokenController $passport,
        string $grantType,
    ): Response {
        $body = $request->getParsedBody();
        $body = is_array($body) ? $body : [];

        return $passport->issueToken(
            $request->withParsedBody([...$body, 'grant_type' => $grantType]),
            $response,
        );
    }
}
