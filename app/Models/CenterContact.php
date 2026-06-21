<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'center_id',
    'platform_code',
    'url',
])]

class CenterContact extends Model
{
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function platform()
    {
        return $this->belongsTo(ContactPlatform::class, 'platform_code', 'code');
    }
}
