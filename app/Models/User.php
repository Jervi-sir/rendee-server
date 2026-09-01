<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    'profile_completed',
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

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class);
    }

    public function getFullImageUrlAttribute(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        return url($this->image_url);
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
            'patient',
            'partner.partnerType',
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

        $partnerType = null;
        if ($this->user_role_code === UserRole::PARTNER || $this->partner) {
            $partnerTypeModel = $this->partner?->partnerType;
            if ($partnerTypeModel) {
                $partnerType = [
                    'code' => $partnerTypeModel->code,
                    'en' => $partnerTypeModel->en,
                    'fr' => $partnerTypeModel->fr,
                    'ar' => $partnerTypeModel->ar,
                ];
            } elseif ($this->partner?->partner_type_code) {
                $partnerType = [
                    'code' => $this->partner->partner_type_code,
                    'en' => $this->partner->partner_type_code,
                    'fr' => $this->partner->partner_type_code,
                    'ar' => $this->partner->partner_type_code,
                ];
            }
        }

        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'image_url' => $this->full_image_url,
            'profile_completed' => (bool) $this->profile_completed,
            'created_at' => $this->created_at?->toIso8601String(),
            'user_role_code' => $this->user_role_code,
            'user_role' => $this->userRole ? [
                'code' => $this->userRole->code,
                'en' => $this->userRole->en,
                'fr' => $this->userRole->fr,
                'ar' => $this->userRole->ar,
            ] : null,
            'partner_type' => $partnerType,
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
