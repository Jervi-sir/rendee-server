<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'label',
    'city',
    'speciality_code',
])]

class RecentSearch extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class, 'speciality_code', 'code');
    }
}
