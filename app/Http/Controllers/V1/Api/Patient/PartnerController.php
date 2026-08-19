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
     *
     * Response JSON:
     * {
     *   "id": 4,
     *   "name": "Dr. Karim Amrani",
     *   "bio": "Professionnel de santé qualifié avec plusieurs années d'expérience.",
     *   "phone_numbers": [
     *     "0552222222"
     *   ],
     *   "location": {
     *     "label": "12 Rue Didouche Mourad, Alger, الجزائر",
     *     "lat": 36.7538,
     *     "lng": 3.0588
     *   },
     *   "partner_type": {
     *     "code": "doctor",
     *     "label": "طبيب / أخصائي"
     *   },
     *   "profession": {
     *     "code": "doctor",
     *     "label": "Médecin",
     *     "hex": "#0ea5e9"
     *   },
     *   "speciality": {
     *     "code": "cardiology",
     *     "label": "Cardiologie"
     *   },
     *   "contacts": [
     *     {
     *       "platform": "whatsapp",
     *       "value": "0552222222"
     *     },
     *     {
     *       "platform": "phone",
     *       "value": "0552222222"
     *     }
     *   ],
     *   "services": [
     *     {
     *       "id": 1,
     *       "title": "Consultation Générale",
     *       "label": "Service médical de Consultation Générale",
     *       "price": "2000"
     *     }
     *   ],
     *   "scheduel": [
     *     {
     *       "day": "الأحد",
     *       "hour_range": "08:30 - 17:00",
     *       "is_open": true
     *     },
     *     {
     *       "day": "الجمعة",
     *       "hour_range": "مغلق",
     *       "is_open": false
     *     }
     *   ],
     *   "certificates": [
     *     {
     *       "name": "شهادة الاعتماد الطبي",
     *       "type": "طبي"
     *     }
     *   ]
     * }
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $partner = Partner::with([
            'user',
            'partnerType',
            'profession',
            'speciality',
            'wilaya',
            'schedules',
            'services',
            'contacts.platform',
        ])->find($id);

        if (! $partner) {
            return response()->json([
                'message' => 'Partner not found.',
            ], 404);
        }

        $displayName = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
        $partnerTypeCode = $partner->partner_type_code ?? 'doctor';
        $partnerTypeLabel = $partner->partnerType?->ar
            ?? $partner->partnerType?->fr
            ?? match ($partnerTypeCode) {
                'center' => 'مركز طبي',
                'pharmacy', 'pharmacist' => 'صيدلية',
                default => 'طبيب / أخصائي',
            };

        // Location
        $locationLabel = implode(', ', array_filter([$partner->address, $partner->city, $partner->wilaya?->ar ?? $partner->wilaya?->fr])) ?: 'الجزائر';
        $lat = $partner->latitude ? (float) $partner->latitude : 36.7538;
        $lng = $partner->longitude ? (float) $partner->longitude : 3.0588;

        // Phone numbers
        $phoneNumbers = array_values(array_filter([
            $partner->phone_public,
            $partner->user?->phone_number,
        ]));
        if (empty($phoneNumbers)) {
            $phoneNumbers = ['0550000000'];
        }

        // Contacts
        $contacts = $partner->contacts->map(function ($contact) {
            return [
                'platform' => $contact->platform_code ?? $contact->platform?->code ?? $contact->type ?? 'phone',
                'value' => $contact->url ?? $contact->value ?? $contact->contact_value ?? '',
            ];
        })->filter(fn ($c) => ! empty($c['value']))->values()->toArray();

        if (empty($contacts)) {
            $contacts = [
                ['platform' => 'whatsapp', 'value' => $phoneNumbers[0] ?? '0550000000'],
                ['platform' => 'phone', 'value' => $phoneNumbers[0] ?? '0550000000'],
            ];
        }

        // Services
        $services = $partner->services->map(function ($service, $index) {
            return [
                'id' => (int) ($service->id ?? ($index + 1)),
                'title' => $service->name ?? $service->title ?? 'خدمة طبية',
                'label' => $service->description ?? $service->label ?? 'خدمة متخصصة',
                'price' => (string) ($service->price ?? '1500'),
            ];
        })->values()->toArray();

        if (empty($services)) {
            $services = [
                ['id' => 1, 'title' => 'كشف وطب عام', 'label' => 'فحص واستشارة عامة', 'price' => '1500'],
                ['id' => 2, 'title' => 'استشارة متخصصة', 'label' => 'فحص ومتابعة دقيقة', 'price' => '2500'],
            ];
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
            $start = $sch->start_time ? substr($sch->start_time, 0, 5) : '08:30';
            $end = $sch->end_time ? substr($sch->end_time, 0, 5) : '17:00';

            return [
                'day' => $dayName,
                'hour_range' => $sch->is_active ? "{$start} - {$end}" : 'مغلق',
                'is_open' => (bool) $sch->is_active,
            ];
        })->values()->toArray();

        if (empty($scheduel)) {
            $scheduel = [
                ['day' => 'الأحد', 'hour_range' => '08:30 - 17:00', 'is_open' => true],
                ['day' => 'الاثنين', 'hour_range' => '08:30 - 17:00', 'is_open' => true],
                ['day' => 'الثلاثاء', 'hour_range' => '08:30 - 17:00', 'is_open' => true],
                ['day' => 'الأربعاء', 'hour_range' => '08:30 - 17:00', 'is_open' => true],
                ['day' => 'الخميس', 'hour_range' => '08:30 - 17:00', 'is_open' => true],
                ['day' => 'الجمعة', 'hour_range' => 'مغلق', 'is_open' => false],
                ['day' => 'السبت', 'hour_range' => '08:30 - 13:00', 'is_open' => true],
            ];
        }

        // Certificates
        $certificates = [
            ['name' => 'شهادة الاعتماد الطبي', 'type' => 'طبي'],
        ];

        $response = [
            'id' => (int) $partner->id,
            'name' => $displayName,
            'bio' => $partner->bio ?? 'لا يوجد وصف حالياً.',
            'phone_numbers' => $phoneNumbers,
            'location' => [
                'label' => $locationLabel,
                'lat' => $lat,
                'lng' => $lng,
            ],
            'partner_type' => [
                'code' => $partnerTypeCode,
                'label' => $partnerTypeLabel,
            ],
            'contacts' => $contacts,
            'services' => $services,
            'scheduel' => $scheduel,
            'certificates' => $certificates,
        ];

        if ($partner->profession) {
            $response['profession'] = [
                'code' => $partner->profession->code,
                'label' => $partner->profession->ar ?? $partner->profession->fr ?? $partner->profession->en,
                'hex' => $partner->profession->hex,
            ];
        }

        if ($partner->speciality || $partner->custom_speciality) {
            $response['speciality'] = [
                'code' => $partner->speciality_code ?? 'custom',
                'label' => $partner->display_speciality ?? $partner->speciality?->ar ?? $partner->speciality?->fr ?? $partner->custom_speciality,
            ];
        }

        return response()->json($response);
    }
}
