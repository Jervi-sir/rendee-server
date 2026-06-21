<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'en', 'fr', 'ar'])]

class UserRole extends Model
{
    public function users()
    {
        return $this->hasMany(User::class, 'user_role_code', 'code');
    }
}
