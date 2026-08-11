<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'wilaya_code',
    'name',
    'phone_public',
    'bio',
    'address',
    'city',
    'latitude',
    'longitude',
    'is_available',
])]
class Pharmacist extends Model
{
    protected $table = 'pharmacists';

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_available' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }
}
