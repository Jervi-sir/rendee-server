<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'en',
    'fr',
    'ar',
])]
class PartnerType extends Model
{
    public const DOCTOR = 'doctor';

    public const DENTIST = 'dentist';

    public const PHARMACIST = 'pharmacist';

    public const CENTER = 'center';

    public const PSY = 'psy';

    public const PROFESSIONAL = 'professional';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'partner_type_code', 'code');
    }

    public function professions(): HasMany
    {
        return $this->hasMany(Profession::class, 'partner_type_code', 'code');
    }
}
