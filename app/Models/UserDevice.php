<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['*'])]

class UserDevice extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'last_active_at' => 'datetime',
            'last_logged_in_at' => 'datetime',
            'push_token_last_refreshed_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'push_notifications_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
