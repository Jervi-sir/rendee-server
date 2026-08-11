<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'name',
    'wilaya_code',
    'phone_public',
    'bio',
    'address',
    'city',
    'latitude',
    'longitude',
    'is_available',
])]
class Pharmacy extends Model
{
    protected $table = 'pharmacists';

    public function getLocationAttribute(): string
    {
        return implode(', ', array_filter([$this->address, $this->city])) ?: 'وهران';
    }

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(LikeItem::class, 'likeable');
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_code', 'code');
    }

    /**
     * Get contacts relation via user_id.
     */
    public function contacts()
    {
        return $this->hasMany(UserContact::class, 'user_id', 'user_id');
    }

    /**
     * Format pharmacy details for patient-facing APIs.
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name ?? $this->user?->full_name ?? $this->user?->name ?? 'صيدلية',
            'pharmacistName' => $this->user?->full_name ?? $this->user?->name ?? 'صيدلي',
            'type' => 'صيدلية',
            'location' => $this->location,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'bio' => $this->bio ?? 'صيدلية تقدم الخدمات الدوائية والصيدلانية للمرضى.',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_available' => (bool) $this->is_available,
            'is_24_7' => (bool) ($this->is_on_duty ?? false),
            'emergency_label' => ($this->is_on_duty ?? false) ? 'مناوبة 24/7' : 'صيدلية عادية',
            'wilaya_code' => $this->wilaya_code,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
                'phone_number' => $this->user->phone_number,
            ] : null,
        ];

        if ($detailed) {
            $arabicDays = [
                0 => 'الأحد',
                1 => 'الإثنين',
                2 => 'الثلاثاء',
                3 => 'الأربعاء',
                4 => 'الخميس',
                5 => 'الجمعة',
                6 => 'السبت',
            ];

            $weeklySchedule = [];
            foreach ($arabicDays as $dayIndex => $dayName) {
                $weeklySchedule[] = [
                    'day' => $dayName,
                    'isOpen' => ($this->is_on_duty ?? false) || $dayIndex !== 5,
                    'hours' => ($this->is_on_duty ?? false) ? '٢٤ ساعة' : ($dayIndex === 5 ? 'مغلق (عطلة)' : '08:00 - 22:00'),
                ];
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

            $data['services'] = [
                ['id' => 1, 'name' => 'استشارات صيدلانية صرف وتوجيه دواء', 'description' => 'تقديم النصائح والإرشادات حول الأدوية الموصوفة والآثار الجانبية.'],
                ['id' => 2, 'name' => 'قياس ضغط الدم والسكر مجاناً', 'description' => 'فحص سريع لضغط الدم ونسبة السكر لتتبع الحالة الصحية.'],
                ['id' => 3, 'name' => 'مستلزمات العناية والإسعافات الأولية', 'description' => 'توفير الشاش، المعقمات، والأدوات الإسعافية الطارئة.'],
            ];
            $data['contacts'] = $this->relationLoaded('contacts') ? $this->contacts : [];
        }

        return $data;
    }

    /**
     * Format pharmacy as a map marker item.
     */
    public function formatMapMarker(int $index = 0): array
    {
        $title = $this->name ?? $this->user?->full_name ?? $this->user?->name ?? 'صيدلية';
        $lat = $this->latitude ? (float) $this->latitude : (35.6971 + 0.003 * ($index - 3));
        $lng = $this->longitude ? (float) $this->longitude : (-0.6308 + 0.003 * ($index - 3));

        return [
            'id' => $this->id,
            'title' => $title,
            'name' => $title,
            'latitude' => $lat,
            'longitude' => $lng,
            'entity_type' => 'pharmacy',
            'type' => 'pharmacy',
            'entity_id' => $this->id,
            'specialty' => 'صيدلية',
            'phone' => $this->phone_public ?? $this->user?->phone_number,
            'city' => $this->city ?? 'وهران',
            'address' => $this->address ?? 'بير الجير',
            'wilaya_code' => $this->wilaya_code ?? '31',
            'rating' => 4.7,
            'is_available' => (bool) $this->is_available,
            'is_24_7' => (bool) ($this->is_on_duty ?? false),
        ];
    }
}
