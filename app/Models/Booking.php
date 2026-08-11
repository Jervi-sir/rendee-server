<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'reference',
    'patient_id',
    'bookable_type',
    'bookable_id',
    'service_type',
    'service_id',
    'schedule_type',
    'schedule_id',
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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Polymorphic Relations
    |--------------------------------------------------------------------------
    */
    public function bookable()
    {
        return $this->morphTo();
    }

    public function service()
    {
        return $this->morphTo();
    }

    public function schedule()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Common Relations
    |--------------------------------------------------------------------------
    */
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_code', 'code');
    }

    public function bookingHistories()
    {
        return $this->hasMany(BookingHistory::class);
    }

    /**
     * Format booking details for patient-facing APIs.
     */
    public function formatForPatient(bool $detailed = false): array
    {
        $bookableFormatted = null;

        if ($this->relationLoaded('bookable') && $this->bookable) {
            if (method_exists($this->bookable, 'formatForPatient')) {
                $bookableFormatted = $this->bookable->formatForPatient(false);
            } else {
                $bookableFormatted = [
                    'id' => $this->bookable->id,
                    'name' => $this->bookable->name ?? $this->bookable->user?->name ?? '',
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
            'bookable_type' => $this->is_center ? self::TYPE_CENTER : self::TYPE_PROFESSIONAL,
            'bookable_id' => $this->bookable_id,
            'provider' => $bookableFormatted,
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
