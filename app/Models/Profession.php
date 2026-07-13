<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function professionals()
    {
        return $this->hasMany(Professional::class, 'profession_code', 'code');
    }
}
