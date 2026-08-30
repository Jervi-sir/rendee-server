<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'partner_type_code',
    'name',
    'profession_code',
    'speciality_code',
    'custom_speciality',
    'center_catalog_code',
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
    'emergency_24_7',
    'is_on_duty',
    'is_active',
])]
class Partner extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_available' => 'boolean',
            'emergency_24_7' => 'boolean',
            'is_on_duty' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partnerType(): BelongsTo
    {
        return $this->belongsTo(PartnerType::class, 'partner_type_code', 'code');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_code', 'code');
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Speciality::class, 'speciality_code', 'code');
    }

    public function specialty(): BelongsTo
    {
        return $this->speciality();
    }

    public function getDisplaySpecialityAttribute(): ?string
    {
        return $this->speciality?->en ?? $this->custom_speciality;
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(CenterCatalog::class, 'center_catalog_code', 'code');
    }

    public function centerCatalog(): BelongsTo
    {
        return $this->catalog();
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PartnerSchedule::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(PartnerService::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class, 'user_id', 'user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(LikedPartner::class);
    }

    public function getLocationAttribute(): string
    {
        return implode(', ', array_filter([$this->address, $this->city])) ?: 'وهران';
    }

    /**
     * Format partner details for patient-facing APIs.
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $specialtyName = $this->specialty?->ar ?? $this->specialty?->en ?? null;
        $professionName = $this->profession?->ar ?? $this->profession?->en ?? null;
        $catalogName = $this->catalog?->ar ?? $this->catalog?->en ?? null;

        $displayName = $this->name ?? $this->user?->full_name ?? $this->user?->name ?? 'شريك';

        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'partner_type' => $this->partner_type_code ?? 'doctor',
            'partner_type_code' => $this->partner_type_code ?? 'doctor',
            'name' => $displayName,
            'title' => $professionName ?? $catalogName ?? ($this->partner_type_code === 'pharmacist' ? 'صيدلية' : 'طبيب'),
            'specialty' => $specialtyName,
            'profession_code' => $this->profession_code,
            'speciality_code' => $this->speciality_code,
            'custom_speciality' => $this->custom_speciality,
            'center_catalog_code' => $this->center_catalog_code,
            'license_number' => $this->license_number,
            'years_experience' => $this->years_experience,
            'bio' => $this->bio,
            'description' => $this->bio,
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'address' => $this->address,
            'city' => $this->city,
            'location' => $this->location,
            'wilaya_code' => $this->wilaya_code,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'is_available' => (bool) $this->is_available,
            'emergency_24_7' => (bool) $this->emergency_24_7,
            'is_on_duty' => (bool) $this->is_on_duty,
            'is_active' => (bool) $this->is_active,
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
            $daysOfWeek = [
                0 => ['en' => 'Sunday', 'ar' => 'الأحد', 'fr' => 'Dimanche'],
                1 => ['en' => 'Monday', 'ar' => 'الاثنين', 'fr' => 'Lundi'],
                2 => ['en' => 'Tuesday', 'ar' => 'الثلاثاء', 'fr' => 'Mardi'],
                3 => ['en' => 'Wednesday', 'ar' => 'الأربعاء', 'fr' => 'Mercredi'],
                4 => ['en' => 'Thursday', 'ar' => 'الخميس', 'fr' => 'Jeudi'],
                5 => ['en' => 'Friday', 'ar' => 'الجمعة', 'fr' => 'Vendresse'],
                6 => ['en' => 'Saturday', 'ar' => 'السبت', 'fr' => 'Samedi'],
            ];

            $data['schedules'] = $this->schedules->map(function ($sch) use ($daysOfWeek) {
                $dayMeta = $daysOfWeek[$sch->day_of_week] ?? ['en' => 'Day '.$sch->day_of_week, 'ar' => 'اليوم '.$sch->day_of_week, 'fr' => 'Jour '.$sch->day_of_week];

                return [
                    'id' => $sch->id,
                    'day_of_week' => $sch->day_of_week,
                    'day_name' => $dayMeta['ar'],
                    'day_name_ar' => $dayMeta['ar'],
                    'day_name_fr' => $dayMeta['fr'],
                    'day_name_en' => $dayMeta['en'],
                    'start_time' => $sch->start_time,
                    'end_time' => $sch->end_time,
                    'slot_duration_minutes' => 30,
                    'is_active' => (bool) $sch->is_active,
                ];
            });
            $data['services'] = $this->services;
            $data['contacts'] = $this->contacts;
        }

        return $data;
    }

    /**
     * Format map marker structure.
     */
    public function formatMapMarker(int $index = 0): array
    {
        $type = $this->partner_type_code ?? 'doctor';
        $entityType = match ($type) {
            'center' => 'center',
            'pharmacist' => 'pharmacy',
            default => 'professional',
        };

        $title = $this->name ?? $this->user?->full_name ?? $this->user?->name ?? 'شريك';
        if (in_array($type, ['doctor', 'professional']) && ! str_starts_with($title, 'د.')) {
            $title = 'د. '.$title;
        }

        return [
            'id' => 'm'.($index + 1),
            'latitude' => $this->latitude ? (float) $this->latitude : 35.6969,
            'longitude' => $this->longitude ? (float) $this->longitude : -0.6331,
            'title' => $title,
            'city' => $this->city ?? 'وهران',
            'address' => $this->address ?? $this->city ?? 'الجزائر',
            'entity_type' => $entityType,
            'entity_id' => $this->id,
            'pin_color' => $entityType === 'pharmacy' ? '#059669' : ($entityType === 'center' ? '#1E3A8A' : '#0284C7'),
        ];
    }
}
