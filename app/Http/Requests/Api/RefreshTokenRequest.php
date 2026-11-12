<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'refresh_token' => ['required', 'string'],
            'scope' => ['sometimes', 'string'],
        ];
    }
}
