<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'number',
    'en',
    'fr',
    'ar',
    'lat',
    'lng',
])]

class Wilaya extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'wilaya_code', 'code');
    }

    protected $appends = ['lat', 'lng'];

    public static array $wilayaCoordinates = [
        '01' => ['lat' => 27.8742, 'lng' => -0.2939],
        '02' => ['lat' => 36.1667, 'lng' => 1.3333],
        '03' => ['lat' => 33.8000, 'lng' => 2.8833],
        '04' => ['lat' => 35.8083, 'lng' => 7.1128],
        '05' => ['lat' => 35.5559, 'lng' => 6.1743],
        '06' => ['lat' => 36.7510, 'lng' => 5.0567],
        '07' => ['lat' => 34.8500, 'lng' => 5.7333],
        '08' => ['lat' => 31.6167, 'lng' => -2.2167],
        '09' => ['lat' => 36.4702, 'lng' => 2.8288],
        '10' => ['lat' => 36.3667, 'lng' => 3.9000],
        '11' => ['lat' => 22.7850, 'lng' => 5.5228],
        '12' => ['lat' => 35.4000, 'lng' => 8.1167],
        '13' => ['lat' => 34.8783, 'lng' => -1.3150],
        '14' => ['lat' => 35.3711, 'lng' => 1.3169],
        '15' => ['lat' => 36.7117, 'lng' => 4.0458],
        '16' => ['lat' => 36.7538, 'lng' => 3.0588],
        '17' => ['lat' => 34.6722, 'lng' => 3.2631],
        '18' => ['lat' => 36.8081, 'lng' => 5.7644],
        '19' => ['lat' => 36.1911, 'lng' => 5.4136],
        '20' => ['lat' => 34.8500, 'lng' => 0.1500],
        '21' => ['lat' => 36.8797, 'lng' => 6.9075],
        '22' => ['lat' => 35.2000, 'lng' => -0.6333],
        '23' => ['lat' => 36.9000, 'lng' => 7.7667],
        '24' => ['lat' => 36.4650, 'lng' => 7.4261],
        '25' => ['lat' => 36.3650, 'lng' => 6.6147],
        '26' => ['lat' => 36.2642, 'lng' => 2.7539],
        '27' => ['lat' => 35.9333, 'lng' => 0.0833],
        '28' => ['lat' => 35.7000, 'lng' => 4.5333],
        '29' => ['lat' => 35.3966, 'lng' => 0.1403],
        '30' => ['lat' => 31.9500, 'lng' => 5.3333],
        '31' => ['lat' => 35.6971, 'lng' => -0.6308],
        '32' => ['lat' => 33.6833, 'lng' => 1.0167],
        '33' => ['lat' => 24.2833, 'lng' => 9.4500],
        '34' => ['lat' => 36.4500, 'lng' => 6.0333],
        '35' => ['lat' => 36.9167, 'lng' => 3.5833],
        '36' => ['lat' => 36.7667, 'lng' => 7.8500],
        '37' => ['lat' => 27.6711, 'lng' => -8.1472],
        '38' => ['lat' => 35.6833, 'lng' => 1.8167],
        '39' => ['lat' => 33.3667, 'lng' => 6.8667],
        '40' => ['lat' => 35.5000, 'lng' => 7.1000],
        '41' => ['lat' => 36.2833, 'lng' => 7.9500],
        '42' => ['lat' => 36.5833, 'lng' => 2.4500],
        '43' => ['lat' => 36.4667, 'lng' => 6.2667],
        '44' => ['lat' => 36.2667, 'lng' => 1.9667],
        '45' => ['lat' => 33.2333, 'lng' => 0.3167],
        '46' => ['lat' => 35.2982, 'lng' => -1.1404],
        '47' => ['lat' => 32.4833, 'lng' => 3.6667],
        '48' => ['lat' => 35.7333, 'lng' => 0.5500],
        '49' => ['lat' => 27.8742, 'lng' => -0.2939],
        '50' => ['lat' => 21.3333, 'lng' => 0.9667],
        '51' => ['lat' => 34.8500, 'lng' => 5.7333],
        '52' => ['lat' => 31.6167, 'lng' => -2.2167],
        '53' => ['lat' => 28.5333, 'lng' => 7.0333],
        '54' => ['lat' => 19.5667, 'lng' => 5.7833],
        '55' => ['lat' => 33.1167, 'lng' => 6.0667],
        '56' => ['lat' => 24.5500, 'lng' => 9.4833],
        '57' => ['lat' => 33.3667, 'lng' => 6.8667],
        '58' => ['lat' => 30.5833, 'lng' => 2.8833],
    ];

    public function getLatitudeAttribute(): float
    {
        if (isset($this->attributes['lat']) && $this->attributes['lat'] !== null) {
            return (float) $this->attributes['lat'];
        }

        $codeKey = sprintf('%02d', (int) ($this->code ?? $this->number ?? 31));

        return self::$wilayaCoordinates[$codeKey]['lat'] ?? 35.6971;
    }

    public function getLongitudeAttribute(): float
    {
        if (isset($this->attributes['lng']) && $this->attributes['lng'] !== null) {
            return (float) $this->attributes['lng'];
        }

        $codeKey = sprintf('%02d', (int) ($this->code ?? $this->number ?? 31));

        return self::$wilayaCoordinates[$codeKey]['lng'] ?? -0.6308;
    }

    public function getLatAttribute(): float
    {
        return $this->getLatitudeAttribute();
    }

    public function getLngAttribute(): float
    {
        return $this->getLongitudeAttribute();
    }

    public static function getActiveWilayas()
    {
        $activeWilayaCodes = Partner::where('is_active', true)
            ->where('is_available', true)
            ->whereNotNull('wilaya_code')
            ->pluck('wilaya_code')
            ->unique()
            ->values();

        return self::whereIn('code', $activeWilayaCodes)
            ->orderByRaw('CAST(number AS INTEGER) ASC')
            ->get()
            ->map(function ($w) {
                return [
                    'key' => $w->code,
                    'code' => $w->code,
                    'number' => (int) $w->number,
                    'label' => (int) $w->number.' - '.($w->ar ?? $w->en ?? $w->code),
                    'ar' => $w->ar,
                    'en' => $w->en,
                    'fr' => $w->fr,
                    'lat' => $w->lat,
                    'lng' => $w->lng,
                ];
            })
            ->sortBy('number')
            ->values();
    }
}
