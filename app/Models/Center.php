<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'name',
    'center_catalog_code',
    'license_number',
    'phone_public',
    'description',
    'address',
    'city',
    'emergency_24_7',
    'is_active',
])]

class Center extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'emergency_24_7' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(LikeItem::class, 'likeable');
    }

    public function catalog()
    {
        return $this->belongsTo(CenterCatalog::class, 'center_catalog_code', 'code');
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    public function workingHours()
    {
        return $this->hasMany(CenterWorkingHour::class);
    }

    public function services()
    {
        return $this->hasMany(CenterService::class);
    }

    public function contacts()
    {
        return $this->hasMany(UserContact::class, 'user_id', 'user_id');
    }

    /**
     * Format center details for patient-facing APIs.
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $centerType = $this->catalog?->ar ?? $this->catalog?->en ?? 'مركز طبي';

        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name ?? $this->user?->full_name ?? $this->user?->name ?? 'مركز طبي',
            'type' => $centerType,
            'center_catalog_code' => $this->center_catalog_code,
            'license_number' => $this->license_number,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'wilaya_code' => $this->wilaya_code,
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'emergency_24_7' => (bool) $this->emergency_24_7,
            'emergency_label' => $this->emergency_24_7 ? 'متاح' : 'غير متاح',
            'is_active' => (bool) $this->is_active,
            'image_url' => $this->user?->image_url,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
                'phone_number' => $this->user->phone_number,
                'image_url' => $this->user->image_url,
            ] : null,
        ];

        if ($detailed) {
            $data['services'] = $this->relationLoaded('services') ? $this->services->map(function ($s) {
                return [
                    'id' => $s->id,
                    'service_catalog_code' => $s->service_catalog_code,
                    'name' => $s->serviceCatalog?->ar ?? $s->serviceCatalog?->en ?? 'خدمة طبية',
                    'price' => $s->price,
                    'duration_minutes' => $s->duration_minutes,
                ];
            })->values()->all() : [];

            $data['working_hours_slots'] = $this->relationLoaded('workingHours') ? $this->workingHours->map(function ($wh) {
                return [
                    'id' => $wh->id,
                    'day_of_week' => $wh->day_of_week,
                    'start_time' => $wh->start_time,
                    'end_time' => $wh->end_time,
                    'is_active' => (bool) ($wh->is_active ?? true),
                ];
            })->values()->all() : [];

            $arabicDays = [
                0 => 'الأحد',
                1 => 'الإثنين',
                2 => 'الثلاثاء',
                3 => 'الأربعاء',
                4 => 'الخميس',
                5 => 'الجمعة',
                6 => 'السبت',
            ];

            $workingHoursByDay = [];
            if ($this->relationLoaded('workingHours')) {
                foreach ($this->workingHours as $wh) {
                    $workingHoursByDay[$wh->day_of_week] = $wh;
                }
            }

            $weeklySchedule = [];
            foreach ($arabicDays as $dayIndex => $dayName) {
                if (isset($workingHoursByDay[$dayIndex])) {
                    $wh = $workingHoursByDay[$dayIndex];
                    $start = $wh->start_time ? substr((string) $wh->start_time, 0, 5) : '08:00';
                    $end = $wh->end_time ? substr((string) $wh->end_time, 0, 5) : '22:00';
                    $weeklySchedule[] = [
                        'day' => $dayName,
                        'isOpen' => (bool) ($wh->is_active ?? true),
                        'hours' => ($wh->is_active ?? true) ? ($this->emergency_24_7 ? '٢٤ ساعة' : "{$start} - {$end}") : 'مغلق (عطلة)',
                    ];
                } else {
                    $weeklySchedule[] = [
                        'day' => $dayName,
                        'isOpen' => $this->emergency_24_7 || $dayIndex !== 5,
                        'hours' => $this->emergency_24_7 ? '٢٤ ساعة' : ($dayIndex === 5 ? 'مغلق (عطلة)' : '08:00 - 22:00'),
                    ];
                }
            }
            $data['weekly_schedule'] = $weeklySchedule;

            $socialMedia = [
                'whatsapp' => null,
                'facebook' => null,
                'tiktok' => null,
                'phone' => $this->phone_public ?? $this->user?->phone_number,
            ];

            if ($this->relationLoaded('contacts') && $this->contacts) {
                foreach ($this->contacts as $contact) {
                    $platform = strtolower((string) ($contact->platform_code ?? $contact->contact_platform_code ?? ''));
                    $val = $contact->url ?? $contact->value ?? $contact->contact_value ?? null;
                    if ($platform === 'whatsapp') {
                        $socialMedia['whatsapp'] = $val;
                    } elseif ($platform === 'facebook') {
                        $socialMedia['facebook'] = $val;
                    } elseif ($platform === 'tiktok') {
                        $socialMedia['tiktok'] = $val;
                    }
                }
            }
            $data['social_media'] = $socialMedia;

            $data['contacts'] = $this->relationLoaded('contacts') ? $this->contacts : [];
        }

        return $data;
    }

    /**
     * Format medical center as a map marker item.
     */
    public function formatMapMarker(int $index = 0): array
    {
        $title = $this->name ?? $this->user?->full_name ?? $this->user?->name ?? 'مركز طبي';
        $lat = $this->latitude ? (float) $this->latitude : (35.6971 + 0.004 * ($index - 1));
        $lng = $this->longitude ? (float) $this->longitude : (-0.6308 - 0.004 * ($index - 1));

        return [
            'id' => $this->id,
            'title' => $title,
            'name' => $title,
            'latitude' => $lat,
            'longitude' => $lng,
            'entity_type' => 'center',
            'type' => 'center',
            'entity_id' => $this->id,
            'specialty' => $this->catalog?->ar ?? 'مركز طبي متكامل',
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'city' => $this->city ?? 'وهران',
            'address' => $this->address ?? 'بير الجير',
            'wilaya_code' => $this->wilaya_code ?? '31',
            'rating' => 4.8,
            'is_available' => (bool) $this->is_active,
            'is_24_7' => (bool) $this->emergency_24_7,
        ];
    }
}
