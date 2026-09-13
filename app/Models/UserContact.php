<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'platform_code',
    'url',
    'value',
    'target_user_type',
])]
class UserContact extends Model
{
    /**
     * @return BelongsTo<User, UserContact>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ContactPlatform, UserContact>
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(ContactPlatform::class, 'platform_code', 'code');
    }

    public function getValueAttribute(): ?string
    {
        return $this->url;
    }

    public function setValueAttribute(?string $value): void
    {
        $this->attributes['url'] = $value;
    }
}
