<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * GET /api/v1/patient/partners/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $partner = Partner::with([
            'user',
            'profession',
            'speciality',
            'wilaya',
            'commune',
            'schedules',
            'services.catalog',
            'contacts.platform',
        ])->find($id);

        if (! $partner) {
            return response()->json([
                'message' => 'Partner not found.',
            ], 404);
        }

        $displayName = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
        $professionCode = $partner->profession_code ?? 'doctor';
        $professionLabel = $partner->profession?->ar
            ?? $partner->profession?->fr
            ?? $partner->profession?->en
            ?? match ($professionCode) {
                'center' => 'مركز طبي',
                'pharmacy', 'pharmacist' => 'صيدلية',
                default => 'طبيب / أخصائي',
            };

        // Location
        $communeLabel = $partner->commune?->ar ?? $partner->commune?->fr ?? $partner->commune?->en ?? $partner->city;
        $locationLabel = implode(', ', array_filter([$partner->address, $communeLabel, $partner->wilaya?->ar ?? $partner->wilaya?->fr])) ?: 'الجزائر';
        $lat = $partner->lat !== null ? (float) $partner->lat : null;
        $lng = $partner->lng !== null ? (float) $partner->lng : null;

        // Phone numbers
        $phoneNumbers = array_values(array_filter([
            $partner->phone_public,
            $partner->user?->phone_number,
        ]));

        // Contacts
        $contacts = $partner->contacts->map(function ($contact) {
            $val = $contact->url ?? $contact->value ?? '';

            return [
                'id' => $contact->id,
                'platform' => $contact->platform_code ?? $contact->platform?->code ?? 'phone',
                'value' => $val,
                'url' => $val,
            ];
        })->filter(fn ($c) => ! empty($c['value']))->values()->toArray();

        // Services
        $services = $partner->services->map(function ($service, $index) {
            return [
                'id' => (int) ($service->id ?? ($index + 1)),
                'service_catalog_code' => $service->service_catalog_code,
                'title' => $service->catalog?->ar ?? $service->catalog?->en ?? $service->name ?? 'خدمة طبية',
                'label' => $service->description ?? $service->catalog?->en ?? 'خدمة متخصصة',
                'price' => (string) ($service->price ?? '1500'),
                'duration_minutes' => (int) ($service->duration_minutes ?? 30),
            ];
        })->values()->toArray();

        if (empty($services)) {
            $services = [];
        }

        // Scheduel
        $daysMap = [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ];

        $scheduel = $partner->schedules->map(function ($sch) use ($daysMap) {
            $dayName = $daysMap[$sch->day_of_week] ?? ('اليوم '.$sch->day_of_week);

            if (! $sch->is_active) {
                return [
                    'day' => $dayName,
                    'hour_range' => 'مغلق',
                    'is_open' => false,
                ];
            }

            $ranges = [];
            if ($sch->morning_is_active && $sch->morning_start_time && $sch->morning_end_time) {
                $mStart = substr($sch->morning_start_time, 0, 5);
                $mEnd = substr($sch->morning_end_time, 0, 5);
                $ranges[] = "{$mStart} - {$mEnd}";
            }
            if ($sch->evening_is_active && $sch->evening_start_time && $sch->evening_end_time) {
                $eStart = substr($sch->evening_start_time, 0, 5);
                $eEnd = substr($sch->evening_end_time, 0, 5);
                $ranges[] = "{$eStart} - {$eEnd}";
            }

            if (empty($ranges)) {
                $ranges[] = '08:30 - 17:00';
            }

            return [
                'day' => $dayName,
                'hour_range' => implode(' | ', $ranges),
                'is_open' => true,
            ];
        })->values()->toArray();

        if (empty($scheduel)) {
            $scheduel = [];
        }

        // Certificates
        $certificates = [
            ['name' => 'شهادة الاعتماد الطبي', 'type' => 'طبي'],
        ];

        $imageUrl = $this->formatImageUrl($partner->user?->image_url);

        $specialityLabel = $partner->speciality?->ar
            ?? $partner->speciality?->fr
            ?? $partner->custom_speciality
            ?? $partner->display_speciality
            ?? $partner->speciality?->en
            ?? $professionLabel;

        $specialityCode = $partner->speciality_code ?? ($partner->custom_speciality ? 'custom' : null);

        $response = [
            'id' => (int) $partner->id,
            'name' => $displayName,
            'image_url' => $imageUrl,
            'profile_pic' => $imageUrl,
            'avatar' => $imageUrl,
            'image' => $imageUrl,
            'bio' => $partner->bio ?? 'لا يوجد وصف حالياً.',
            'phone_numbers' => $phoneNumbers,
            'location' => [
                'label' => $locationLabel,
                'address' => $partner->address,
                'city' => $partner->city ?? $communeLabel,
                'commune_id' => $partner->commune_id,
                'commune_code' => $partner->commune?->code,
                'wilaya' => $partner->wilaya?->ar ?? $partner->wilaya?->fr ?? null,
                'wilaya_code' => $partner->wilaya_code,
                'lat' => $lat,
                'lng' => $lng,
            ],
            'profession' => [
                'code' => $professionCode,
                'label' => $professionLabel,
                'hex' => $partner->profession?->hex,
            ],
            'speciality' => [
                'code' => $specialityCode,
                'label' => $specialityLabel,
            ],
            'contacts' => $contacts,
            'services' => $services,
            'scheduel' => $scheduel,
            'certificates' => $certificates,
        ];

        return response()->json($response);
    }

    /**
     * Format image URL to always return an absolute/full URL.
     */
    private function formatImageUrl(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        return url($imageUrl);
    }
}
