<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'en', 'fr', 'ar'])]
class UserRole extends Model
{
    public const ADMIN = 'admin';

    public const PATIENT = 'patient';

    public const PARTNER = 'partner';

    // Aliases for backwards compatibility
    public const DOCTOR = 'partner';

    public const DENTIST = 'partner';

    public const PHARMACIST = 'partner';

    public const CENTER = 'partner';

    public const PSY = 'partner';

    public const PROFESSIONAL = 'partner';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_role_code', 'code');
    }
}
