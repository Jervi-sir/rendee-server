<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LikedPartner extends Model
{
    use HasFactory;

    protected $table = 'liked_partners';

    protected $fillable = [
        'user_id',
        'partner_id',
    ];

    /**
     * Get the user who liked the partner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the liked partner.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
