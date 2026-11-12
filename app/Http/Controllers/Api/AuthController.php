<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return response()->json([
            'message' => 'Tạo tài khoản thành công. Hãy đăng nhập để nhận token.',
            'user' => $user,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        $token->revoke();
        $token->refreshToken?->revoke();

        return response()->json(['message' => 'Đăng xuất thành công.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
