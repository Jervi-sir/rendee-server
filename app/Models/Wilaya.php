<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'number',
    'en',
    'fr',
    'ar',
])]

class Wilaya extends Model
{
    //
}
