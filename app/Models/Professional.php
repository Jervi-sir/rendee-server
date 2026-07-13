<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'profession_code',
    'user_id',
    'speciality_code',
    'license_number',
    'years_experience',
    'phone_public',
    'bio',
    'address',
    'city',
    'is_available'
])]

class Professional extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialty()
    {
        return $this->belongsTo(ProfessionalSpeciality::class, 'speciality_code', 'code');
    }

    public function schedules()
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    public function contacts()
    {
        return $this->hasMany(UserContact::class);
    }

    public function services()
    {
        return $this->hasMany(ProfessionalService::class);
    }

    /**
     * Format the professional profile details for patient-facing APIs.
     *
     * @param bool $detailed
     * @return array
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $data = [
            'id' => $this->id,
            'name' => ($this->profession_code === 'doctor' ? 'د. ' : '') . ($this->user?->full_name ?? $this->user?->name ?? 'أخصائي'),
            'profession_code' => $this->profession_code,
            'speciality' => $this->specialty?->ar ?? $this->specialty?->en ?? 'عام',
            'years_experience' => (int) $this->years_experience,
            'rating' => '4.8',
            'reviews_count' => 120,
            'patients_label' => '500+',
            'bio' => $this->bio ?? 'أخصائي متميز في مجاله المهني، حاصل على شهادات عليا وخبرة عملية طويلة.',
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone_public,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
            ] : null,
        ];

        if ($detailed) {
            $data['working_hours'] = $this->relationLoaded('schedules') && $this->schedules->where('is_active', true)->isNotEmpty()
                ? 'متاح حسب الحجوزات اليومية'
                : 'غير محدد';

            $data['qualifications'] = [
                ['id' => 1, 'label' => 'شهادة التخصص الوطنية'],
                ['id' => 2, 'label' => 'عضوية الجمعية المهنية الوطنية'],
            ];

            $data['services'] = $this->relationLoaded('services') ? $this->services : [];
            $data['schedules'] = $this->relationLoaded('schedules') ? $this->schedules : [];
            $data['contacts'] = $this->relationLoaded('contacts') ? $this->contacts : [];
        }

        return $data;
    }
}
