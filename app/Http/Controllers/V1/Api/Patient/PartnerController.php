<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display detailed profile for a specific partner matching partnerType JSON format.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $partner = Partner::with([
            'user',
            'partnerType',
            'profession',
            'specialty',
            'wilaya',
            'schedules',
            'services',
            'contacts',
        ])->find($id);

        if (! $partner) {
            return response()->json([
                'message' => 'Partner not found.',
            ], 404);
        }

        $displayName = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
        $partnerTypeCode = $partner->partner_type_code ?? 'doctor';
        $partnerTypeLabel = $partner->partnerType?->label
            ?? match ($partnerTypeCode) {
                'center' => 'مركز طبي',
                'pharmacy', 'pharmacist' => 'صيدلية',
                default => 'طبيب / أخصائي',
            };

        // Location
        $locationLabel = implode(', ', array_filter([$partner->address, $partner->city, $partner->wilaya?->ar])) ?: 'وهران';
        $lat = $partner->latitude ? (float) $partner->latitude : 35.6969;
        $lng = $partner->longitude ? (float) $partner->longitude : -0.6331;

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
                'platform' => $contact->platform ?? $contact->type ?? 'phone',
                'value' => $contact->value ?? $contact->contact_value ?? '',
            ];
        })->toArray();

        if (empty($contacts)) {
            $contacts = [
                ['platform' => 'whatsapp', 'value' => $phoneNumbers[0] ?? '0550000000'],
                ['platform' => 'phone', 'value' => $phoneNumbers[0] ?? '0550000000'],
            ];
        }

        // Services
        $services = $partner->services->map(function ($service) {
            return [
                'title' => $service->name ?? $service->title ?? 'خدمة طبية',
                'label' => $service->description ?? $service->label ?? 'خدمة متخصصة',
                'price' => (string) ($service->price ?? '1500'),
            ];
        })->toArray();

        if (empty($services)) {
            $services = [
                ['title' => 'كشف وطب عام', 'label' => 'فحص واستشارة عامة', 'price' => '1500'],
                ['title' => 'استشارة متخصصة', 'label' => 'فحص ومتابعة دقيقة', 'price' => '2500'],
            ];
        }

        // Scheduel (Notice spelling matches partnerType interface)
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
            $start = $sch->start_time ? substr($sch->start_time, 0, 5) : '08:00';
            $end = $sch->end_time ? substr($sch->end_time, 0, 5) : '18:00';

            return [
                'day' => $dayName,
                'hour_range' => "{$start} - {$end}",
                'is_open' => (bool) $sch->is_active,
            ];
        })->toArray();

        if (empty($scheduel)) {
            $scheduel = [
                ['day' => 'الأحد', 'hour_range' => '08:00 - 18:00', 'is_open' => true],
                ['day' => 'الاثنين', 'hour_range' => '08:00 - 18:00', 'is_open' => true],
                ['day' => 'الثلاثاء', 'hour_range' => '08:00 - 18:00', 'is_open' => true],
                ['day' => 'الأربعاء', 'hour_range' => '08:00 - 18:00', 'is_open' => true],
                ['day' => 'الخميس', 'hour_range' => '08:00 - 18:00', 'is_open' => true],
                ['day' => 'الجمعة', 'hour_range' => 'مغلق', 'is_open' => false],
                ['day' => 'السبت', 'hour_range' => '08:00 - 16:00', 'is_open' => true],
            ];
        }

        // Certificates
        $certificates = [
            ['name' => 'شهادة الاعتماد الطبي', 'type' => 'طبي'],
        ];

        return response()->json([
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
        ]);
    }
}
