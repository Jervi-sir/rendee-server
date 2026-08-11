<?php

namespace App\Services\V1;

use App\Models\User;

class UserService
{
    /**
     * Eager load the role-specific profile relations on the User model.
     */
    public function loadProfileRelations(User $user): User
    {
        return $user->load([
            'userRole',
            'userDevice',
            'professional',
            'center',
            'patient',
            'pharmacy',
        ]);
    }

    /**
     * Format the standard login/register JSON response with access token.
     */
    public function formatAuthResponse(User $user, string $token, string $message = 'Success'): array
    {
        return [
            'message' => $message,
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->loadProfileRelations($user),
        ];
    }
}
