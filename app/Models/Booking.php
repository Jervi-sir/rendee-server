<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'reference',
    'patient_id',
    'partner_id',
    'partner_service_id',
    'partner_schedule_id',
    'patient_name',
    'patient_phone',
    'booking_date',
    'booking_time',
    'status_code',
    'is_center',
    'proposed_date',
    'proposed_time',
    'has_pending_proposal',
    'notes',
])]
class Booking extends Model
{
    public const TYPE_PROFESSIONAL = 'professional';

    public const TYPE_CENTER = 'center';

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'booking_time' => 'string',
            'proposed_date' => 'date:Y-m-d',
            'proposed_time' => 'string',
            'is_center' => 'boolean',
            'has_pending_proposal' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function partnerService(): BelongsTo
    {
        return $this->belongsTo(PartnerService::class, 'partner_service_id');
    }

    public function service(): BelongsTo
    {
        return $this->partnerService();
    }

    public function partnerSchedule(): BelongsTo
    {
        return $this->belongsTo(PartnerSchedule::class, 'partner_schedule_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->partnerSchedule();
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_code', 'code');
    }

    public function bookingHistories(): HasMany
    {
        return $this->hasMany(BookingHistory::class);
    }

    /**
     * Format booking details for patient-facing APIs.
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $partnerFormatted = null;

        if ($this->relationLoaded('partner') && $this->partner) {
            if (method_exists($this->partner, 'formatForPatient')) {
                $partnerFormatted = $this->partner->formatForPatient($detailed);
            } else {
                $partnerFormatted = [
                    'id' => $this->partner->id,
                    'name' => $this->partner->name ?? $this->partner->user?->name ?? '',
                ];
            }
        }

        $serviceFormatted = null;
        if ($this->relationLoaded('service') && $this->service) {
            $serviceName = $this->service->serviceCatalog?->ar
                ?? $this->service->serviceCatalog?->en
                ?? $this->service->name
                ?? 'خدمة طبية';
            $serviceFormatted = [
                'id' => $this->service->id,
                'name' => $serviceName,
                'price' => $this->service->price ?? null,
                'duration_minutes' => $this->service->duration_minutes ?? null,
            ];
        }

        $data = [
            'id' => $this->id,
            'reference' => $this->reference,
            'patient_id' => $this->patient_id,
            'partner_id' => $this->partner_id,
            'bookable_type' => $this->is_center ? self::TYPE_CENTER : self::TYPE_PROFESSIONAL,
            'bookable_id' => $this->partner_id,
            'provider' => $partnerFormatted,
            'service' => $serviceFormatted,
            'patient_name' => $this->patient_name,
            'patient_phone' => $this->patient_phone,
            'booking_date' => $this->booking_date ? (is_string($this->booking_date) ? $this->booking_date : $this->booking_date->format('Y-m-d')) : null,
            'booking_time' => $this->booking_time,
            'status_code' => $this->status_code,
            'is_center' => (bool) $this->is_center,
            'proposed_date' => $this->proposed_date ? (is_string($this->proposed_date) ? $this->proposed_date : $this->proposed_date->format('Y-m-d')) : null,
            'proposed_time' => $this->proposed_time,
            'has_pending_proposal' => (bool) $this->has_pending_proposal,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        if ($detailed) {
            $data['status'] = $this->relationLoaded('status') && $this->status ? [
                'code' => $this->status->code,
                'en' => $this->status->en,
                'ar' => $this->status->ar,
                'fr' => $this->status->fr,
            ] : null;

            $data['schedule'] = $this->relationLoaded('schedule') && $this->schedule ? [
                'id' => $this->schedule->id,
                'day_of_week' => $this->schedule->day_of_week ?? null,
                'start_time' => $this->schedule->start_time ?? null,
                'end_time' => $this->schedule->end_time ?? null,
            ] : null;

            $data['histories'] = $this->relationLoaded('bookingHistories') ? $this->bookingHistories->map(function ($h) {
                return [
                    'id' => $h->id,
                    'status_code' => $h->status_code,
                    'notes' => $h->notes,
                    'created_at' => $h->created_at?->toIso8601String(),
                ];
            }) : [];
        }

        return $data;
    }
}
