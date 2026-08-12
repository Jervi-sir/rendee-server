<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'partner_id',
    'service_catalog_code',
    'name',
    'description',
    'price',
    'duration_minutes',
    'is_active',
])]
class PartnerService extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_code', 'code');
    }
}
