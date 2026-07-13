<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'en',
    'fr',
    'ar',
])]

class ContactPlatform extends Model
{
    public function userContacts()
    {
        return $this->hasMany(UserContact::class, 'platform_code', 'code');
    }
}
