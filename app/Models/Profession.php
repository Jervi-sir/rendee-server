<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profession extends Model
{
    public const DOCTOR = 'doctor';

    public const PSYCHOLOGIST = 'psychologist';

    public const DENTIST = 'dentist';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'en',
        'fr',
        'ar',
        'hex',
    ];

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'profession_code', 'code');
    }

    public function professionals(): HasMany
    {
        return $this->partners();
    }
}
