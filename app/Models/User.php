<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_role_code',
    'name',
    'email',
    'password',
    'password_plaintext',
    'full_name',
    'image_url',
    'phone_number',
    'profile_complete',
])]
#[Hidden(['password', 'password_plaintext', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function userRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_code', 'code');
    }

    public function userDevice(): HasOne
    {
        return $this->hasOne(UserDevice::class);
    }

    public function professional(): HasOne
    {
        return $this->hasOne(Professional::class);
    }

    public function center(): HasOne
    {
        return $this->hasOne(Center::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function pharmacy(): HasOne
    {
        return $this->hasOne(Pharmacy::class);
    }

    /**
     * Eager-load all role-specific profile relations.
     *
     * @return $this
     */
    public function loadProfileRelations(): static
    {
        return $this->load([
            'userRole',
            'userDevice',
            'professional',
            'center',
            'patient',
            'pharmacy',
        ]);
    }

    /**
     * Format explicit array representation of user with profile relations.
     *
     * @return array<string, mixed>
     */
    public function toFormattedUserArray(): array
    {
        $this->loadProfileRelations();

        return array_filter([
            'id' => $this->id,
            'user_role_code' => $this->user_role_code,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'profile_complete' => (bool) $this->profile_complete,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user_role' => $this->userRole ? [
                'code' => $this->userRole->code,
                'en' => $this->userRole->en,
                'fr' => $this->userRole->fr,
                'ar' => $this->userRole->ar,
            ] : null,
        ], fn ($value) => $value !== null);
    }

    /**
     * Format the standard auth JSON response (login / register / me).
     *
     * @return array{message: string, token_type?: string, access_token?: string, user: array<string, mixed>}
     */
    public function formatAuthResponse(?string $token = null, string $message = 'Success'): array
    {
        $response = [
            'message' => $message,
        ];

        if ($token !== null) {
            $response['token_type'] = 'Bearer';
            $response['access_token'] = $token;
        }

        $response['user'] = $this->toFormattedUserArray();

        return $response;
    }
}
