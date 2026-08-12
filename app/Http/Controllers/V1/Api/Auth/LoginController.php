<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'phone_number' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $phone = $credentials['phone_number'] ?? $credentials['phone'] ?? null;

        if (! $phone) {
            throw ValidationException::withMessages([
                'phone_number' => ['The phone number field is required.'],
            ]);
        }

        $user = User::where('phone_number', $phone)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone_number' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api')->plainTextToken;

        return response()->json(
            $user->formatAuthResponse($token, 'Logged in successfully.')
        );
    }
}
