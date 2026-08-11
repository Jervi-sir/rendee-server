<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'profession_code',
    'user_id',
    'professional_speciality_code',
    'wilaya_code',
    'license_number',
    'years_experience',
    'phone_public',
    'bio',
    'address',
    'city',
    'latitude',
    'longitude',
    'is_available',
])]
class Professional extends Model
{
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

    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(LikeItem::class, 'likeable');
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class, 'profession_code', 'code');
    }

    public function speciality()
    {
        return $this->belongsTo(ProfessionalSpeciality::class, 'professional_speciality_code', 'code');
    }

    public function specialty()
    {
        return $this->speciality();
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    public function schedules()
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    public function services()
    {
        return $this->hasMany(ProfessionalService::class);
    }

    public function contacts()
    {
        return $this->hasMany(UserContact::class, 'user_id', 'user_id');
    }

    /**
     * Format professional details for patient-facing APIs.
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $specialtyName = $this->speciality?->ar ?? $this->speciality?->en ?? null;
        $professionName = $this->profession?->ar ?? $this->profession?->en ?? null;
        $name = $this->user?->full_name ?? $this->user?->name ?? 'طبيب';

        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $name,
            'title' => $professionName ?? 'طبيب',
            'specialty' => $specialtyName,
            'profession_code' => $this->profession_code,
            'professional_speciality_code' => $this->professional_speciality_code,
            'years_experience' => $this->years_experience,
            'bio' => $this->bio,
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'address' => $this->address,
            'city' => $this->city,
            'wilaya_code' => $this->wilaya_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_available' => (bool) $this->is_available,
            'license_number' => $this->license_number,
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
                    'name' => $s->serviceCatalog?->ar ?? $s->serviceCatalog?->en ?? 'خدمة',
                    'price' => $s->price,
                    'duration_minutes' => $s->duration_minutes,
                ];
            })->values()->all() : [];

            $data['schedules'] = $this->relationLoaded('schedules') ? $this->schedules->map(function ($sch) {
                return [
                    'id' => $sch->id,
                    'day_of_week' => $sch->day_of_week,
                    'start_time' => $sch->start_time,
                    'end_time' => $sch->end_time,
                    'is_active' => (bool) $sch->is_active,
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

            $schedulesByDay = [];
            if ($this->relationLoaded('schedules')) {
                foreach ($this->schedules as $sch) {
                    $schedulesByDay[$sch->day_of_week] = $sch;
                }
            }

            $weeklySchedule = [];
            foreach ($arabicDays as $dayIndex => $dayName) {
                if (isset($schedulesByDay[$dayIndex])) {
                    $sch = $schedulesByDay[$dayIndex];
                    $start = $sch->start_time ? substr((string) $sch->start_time, 0, 5) : '09:00';
                    $end = $sch->end_time ? substr((string) $sch->end_time, 0, 5) : '18:00';
                    $weeklySchedule[] = [
                        'day' => $dayName,
                        'isOpen' => (bool) $sch->is_active,
                        'hours' => $sch->is_active ? "{$start} - {$end}" : 'مغلق (عطلة)',
                    ];
                } else {
                    $weeklySchedule[] = [
                        'day' => $dayName,
                        'isOpen' => $dayIndex !== 5,
                        'hours' => $dayIndex === 5 ? 'مغلق (عطلة)' : '09:00 - 18:00',
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
     * Format professional as a map marker item.
     */
    public function formatMapMarker(int $index = 0): array
    {
        $name = $this->user?->full_name ?? $this->user?->name ?? 'طبيب';
        $title = ($this->profession_code === 'doctor' ? 'د. ' : '').$name;
        $specialtyName = $this->speciality?->ar ?? $this->speciality?->en ?? 'طب عام';
        $lat = $this->latitude ? (float) $this->latitude : (35.6971 + 0.005 * ($index - 2));
        $lng = $this->longitude ? (float) $this->longitude : (-0.6308 + 0.005 * ($index - 2));

        return [
            'id' => $this->id,
            'title' => $title,
            'name' => $title,
            'latitude' => $lat,
            'longitude' => $lng,
            'entity_type' => 'doctor',
            'type' => 'doctor',
            'entity_id' => $this->id,
            'specialty' => $specialtyName,
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'city' => $this->city ?? 'وهران',
            'address' => $this->address ?? 'بير الجير',
            'wilaya_code' => $this->wilaya_code ?? '31',
            'rating' => 4.9,
            'is_available' => (bool) $this->is_available,
        ];
    }
}
