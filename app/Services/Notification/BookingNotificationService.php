<?php

namespace App\Services\Notification;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingNotificationService
{
    public function __construct(
        protected PushNotificationService $pushService
    ) {}

    /**
     * Notify Partner when a new booking is booked / created by a patient.
     */
    public function notifyNewBookingCreated(Booking $booking): ?array
    {
        $partnerUser = $booking->partner?->user;
        if (! $partnerUser) {
            return null;
        }

        $patientName = $booking->patient_name ?? $booking->patient?->user?->name ?? 'مريض';
        $formattedDate = $this->formatDate($booking->booking_date);
        $formattedTime = $this->formatTime($booking->booking_time);

        $title = 'موعد جديد بانتظارك 📅';
        $body = "قام {$patientName} بحجز موعد جديد ليوم {$formattedDate} على الساعة {$formattedTime}.";

        $data = [
            'type' => 'booking_created',
            'topic' => 'booking',
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'screen' => 'AppointmentDetails',
            'url' => "rendee://appointments/{$booking->id}",
            'patient_name' => $patientName,
            'booking_date' => (string) $booking->booking_date,
            'booking_time' => (string) $booking->booking_time,
            'status' => $booking->status_code,
        ];

        return $this->pushService->sendToUser(
            user: $partnerUser,
            title: $title,
            body: $body,
            data: $data,
            type: 'booking_created'
        );
    }

    /**
     * Notify Patient when their booking has been confirmed by the partner.
     */
    public function notifyBookingConfirmed(Booking $booking): ?array
    {
        $patientUser = $booking->patient?->user;
        if (! $patientUser) {
            return null;
        }

        $partnerName = $booking->partner?->name ?? 'الطبيب';
        $formattedDate = $this->formatDate($booking->booking_date);
        $formattedTime = $this->formatTime($booking->booking_time);

        $title = 'تم تأكيد موعدك بنجاح ✅';
        $body = "تم تأكيد موعدك مع {$partnerName} ليوم {$formattedDate} الساعة {$formattedTime}.";

        $data = [
            'type' => 'booking_confirmed',
            'topic' => 'booking',
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'screen' => 'BookingDetails',
            'url' => "rendee://bookings/{$booking->id}",
            'partner_name' => $partnerName,
            'booking_date' => (string) $booking->booking_date,
            'booking_time' => (string) $booking->booking_time,
            'status' => Booking::STATUS_CONFIRMED,
        ];

        return $this->pushService->sendToUser(
            user: $patientUser,
            title: $title,
            body: $body,
            data: $data,
            type: 'booking_confirmed'
        );
    }

    /**
     * Notify Partner or Patient when a booking is cancelled.
     */
    public function notifyBookingCancelled(
        Booking $booking,
        ?string $reason = null,
        ?string $cancelledBy = null
    ): ?array {
        $partnerName = $booking->partner?->name ?? 'العيادة';
        $patientName = $booking->patient_name ?? $booking->patient?->user?->name ?? 'المريض';
        $formattedDate = $this->formatDate($booking->booking_date);
        $reasonText = $reason ? " السبب: {$reason}" : '';

        // If cancelled by Partner -> notify Patient
        if ($cancelledBy === 'partner') {
            $patientUser = $booking->patient?->user;
            if (! $patientUser) {
                return null;
            }

            $title = 'تم إلغاء الموعد ❌';
            $body = "تم إلغاء موعدك ليوم {$formattedDate} مع {$partnerName}.{$reasonText}";

            $data = [
                'type' => 'booking_cancelled',
                'topic' => 'booking',
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'screen' => 'BookingDetails',
                'url' => "rendee://bookings/{$booking->id}",
                'cancelled_by' => 'partner',
                'reason' => $reason,
            ];

            return $this->pushService->sendToUser(
                user: $patientUser,
                title: $title,
                body: $body,
                data: $data,
                type: 'booking_cancelled'
            );
        }

        // If cancelled by Patient -> notify Partner
        if ($cancelledBy === 'patient') {
            $partnerUser = $booking->partner?->user;
            if (! $partnerUser) {
                return null;
            }

            $title = 'تم إلغاء موعد ⚠️';
            $body = "قام {$patientName} بإلغاء موعد يوم {$formattedDate}.{$reasonText}";

            $data = [
                'type' => 'booking_cancelled',
                'topic' => 'booking',
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'screen' => 'AppointmentDetails',
                'url' => "rendee://appointments/{$booking->id}",
                'cancelled_by' => 'patient',
                'reason' => $reason,
            ];

            return $this->pushService->sendToUser(
                user: $partnerUser,
                title: $title,
                body: $body,
                data: $data,
                type: 'booking_cancelled'
            );
        }

        return null;
    }

    /**
     * Notify Patient when the partner proposes / suggests a new date or time.
     */
    public function notifyProposalSent(Booking $booking): ?array
    {
        $patientUser = $booking->patient?->user;
        if (! $patientUser) {
            return null;
        }

        $partnerName = $booking->partner?->name ?? 'العيادة';
        $proposedDate = $this->formatDate($booking->proposed_date ?? $booking->booking_date);
        $proposedTime = $this->formatTime($booking->proposed_time ?? $booking->booking_time);

        $title = 'اقتراح موعد بديل ⏱️';
        $body = "اقترح {$partnerName} توقيتاً جديداً لموعدك: {$proposedDate} الساعة {$proposedTime}. يرجى تأكيد الموعد.";

        $data = [
            'type' => 'booking_proposal',
            'topic' => 'booking',
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'screen' => 'BookingDetails',
            'url' => "rendee://bookings/{$booking->id}",
            'proposed_date' => (string) $booking->proposed_date,
            'proposed_time' => (string) $booking->proposed_time,
            'has_pending_proposal' => true,
        ];

        return $this->pushService->sendToUser(
            user: $patientUser,
            title: $title,
            body: $body,
            data: $data,
            type: 'booking_proposal'
        );
    }

    /**
     * Notify Partner when patient confirms the proposed new time.
     */
    public function notifyProposalConfirmed(Booking $booking): ?array
    {
        $partnerUser = $booking->partner?->user;
        if (! $partnerUser) {
            return null;
        }

        $patientName = $booking->patient_name ?? $booking->patient?->user?->name ?? 'المريض';
        $formattedDate = $this->formatDate($booking->booking_date);
        $formattedTime = $this->formatTime($booking->booking_time);

        $title = 'المريض وافق على الموعد المقترح 🤝';
        $body = "وافق {$patientName} على الموعد الجديد ليوم {$formattedDate} الساعة {$formattedTime}.";

        $data = [
            'type' => 'booking_proposal_accepted',
            'topic' => 'booking',
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'screen' => 'AppointmentDetails',
            'url' => "rendee://appointments/{$booking->id}",
            'booking_date' => (string) $booking->booking_date,
            'booking_time' => (string) $booking->booking_time,
            'status' => $booking->status_code,
        ];

        return $this->pushService->sendToUser(
            user: $partnerUser,
            title: $title,
            body: $body,
            data: $data,
            type: 'booking_proposal_accepted'
        );
    }

    /**
     * Notify Patient after booking completion with review / rating prompt.
     */
    public function notifyBookingCompleted(Booking $booking): ?array
    {
        $patientUser = $booking->patient?->user;
        if (! $patientUser) {
            return null;
        }

        $partnerName = $booking->partner?->name ?? 'العيادة';

        $title = 'شكراً لزيارتك ✨';
        $body = "نأمل أن تكون زيارتك لـ {$partnerName} ممتازة. يسعدنا مشاركة تقييمك للخدمة!";

        $data = [
            'type' => 'booking_completed',
            'topic' => 'booking',
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'screen' => 'BookingRating',
            'url' => "rendee://bookings/{$booking->id}/rate",
            'partner_name' => $partnerName,
        ];

        return $this->pushService->sendToUser(
            user: $patientUser,
            title: $title,
            body: $body,
            data: $data,
            type: 'booking_completed'
        );
    }

    /**
     * Helper to format date string or Carbon instance.
     */
    protected function formatDate(mixed $date): string
    {
        if (empty($date)) {
            return '';
        }
        try {
            return Carbon::parse($date)->locale('ar')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    /**
     * Helper to format time string (e.g. 09:30:00 -> 09:30).
     */
    protected function formatTime(?string $time): string
    {
        if (empty($time)) {
            return '';
        }
        return substr($time, 0, 5);
    }
}
