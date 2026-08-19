<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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

    public function users()
    {
        return $this->hasMany(User::class, 'user_role_code', 'code');
    }
}
