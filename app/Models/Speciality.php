<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'profession_code',
    'en',
    'fr',
    'ar',
])]
class Speciality extends Model
{
    protected $table = 'specialities';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_code', 'code');
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'speciality_code', 'code');
    }

    public function professionals(): HasMany
    {
        return $this->partners();
    }
}
