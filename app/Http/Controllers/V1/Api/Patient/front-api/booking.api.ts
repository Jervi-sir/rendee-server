// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE PATIENT - BOOKINGS API CLIENT
 * ============================================================================
 *
 * Provides typed access to patient appointment workflows:
 * - Listing past & upcoming bookings with pagination & status filters
 * - Viewing individual booking details & history logs
 * - Creating new appointment bookings
 * - Pre-filling booking attempts with doctor services, schedules, profession & speciality
 * - Updating/rescheduling pending appointments
 * - Confirming partner-rescheduled proposals
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

export interface BookingProviderUserSummary {
    id: number;
    name: string;
    full_name?: string | null;
    email?: string | null;
    phone_number?: string | null;
    image_url?: string | null;
}

export interface BookingProviderSummary {
    id: number;
    user_id?: number | null;
    name: string;
    title?: string;
    profession?: string | null;
    profession_name?: string | null;
    profession_code?: string | null;
    speciality?: string | null;
    specialty?: string | null;
    speciality_code?: string | null;
    custom_speciality?: string | null;
    license_number?: string | null;
    years_experience?: string | null;
    bio?: string | null;
    description?: string | null;
    phone?: string | null;
    phone_public?: string | null;
    phone_number?: string | null;
    address?: string | null;
    city?: string | null;
    location?: string | null;
    commune_id?: number | null;
    commune_code?: string | null;
    commune?: string | null;
    wilaya_code?: string | null;
    wilaya?: string | null;
    wilaya_name?: string | null;
    lat?: number | null;
    lng?: number | null;
    image_url?: string | null;
    avatar?: string | null;
    profile_pic?: string | null;
    is_available?: boolean;
    emergency_24_7?: boolean;
    is_on_duty?: boolean;
    is_active?: boolean;
    user?: BookingProviderUserSummary | null;
    [key: string]: any;
}

export interface BookingServiceSummary {
    id: number;
    name: string;
    price?: number | string | null;
    duration_minutes?: number | null;
}

export interface BookingStatusInfo {
    code: string;
    ar?: string;
    en?: string;
    fr?: string;
}

export interface BookingScheduleInfo {
    id: number;
    day_of_week?: number | null;
    start_time?: string | null;
    end_time?: string | null;
}

export interface BookingHistoryItem {
    id: number;
    status_code: string;
    notes?: string | null;
    created_at?: string | null;
}

export interface PatientBooking {
    id: number;
    reference: string;
    patient_id: number | null;
    partner_id: number;
    bookable_type?: string;
    bookable_id?: number;
    provider: BookingProviderSummary | null;
    service: BookingServiceSummary | null;
    patient_name: string;
    patient_phone: string;
    booking_date: string;
    booking_time: string;
    status_code: string;
    is_center: boolean;
    proposed_date: string | null;
    proposed_time: string | null;
    has_pending_proposal: boolean;
    notes: string | null;
    created_at: string | null;
}

export interface PatientBookingDetailed extends PatientBooking {
    status?: BookingStatusInfo | null;
    schedule?: BookingScheduleInfo | null;
    histories: BookingHistoryItem[];
}

export interface CreateBookingPayload {
    /** Target partner ID */
    partner_id: number;
    /** Date string in YYYY-MM-DD format (e.g. "2026-08-25") */
    date: string;
    /** Time string (e.g. "10:30" or "10:30:00") */
    time: string;
    /** Selected service ID */
    service_id?: number;
    /** Full name of patient */
    patient_name: string;
    /** Contact phone number */
    patient_phone: string;
    /** Optional notes or symptoms */
    notes?: string;
}

export interface UpdateBookingPayload {
    /** New date string in YYYY-MM-DD format */
    booking_date?: string;
    /** Alias for booking_date */
    date?: string;
    /** New time string (e.g. "14:00") */
    booking_time?: string;
    /** Alias for booking_time */
    time?: string;
    /** Updated notes */
    notes?: string;
}

export interface GetBookingsParams {
    /** Page number (default: 1) */
    page?: number;
    /** Items per page (default: 10) */
    per_page?: number;
    /** Filter by status code (e.g. "confirmed", "pending", "completed") */
    status_code?: string;
}

export interface GetBookingsResponse {
    bookings: PatientBooking[];
    current_page: number;
    next_page: number | null;
    total: number;
}

export interface GetBookingResponse {
    booking: PatientBookingDetailed;
}

export interface CreateBookingResponse {
    success: boolean;
    message: string;
    booking: PatientBookingDetailed;
}

export interface UpdateBookingResponse {
    success: boolean;
    message: string;
    booking: PatientBookingDetailed;
}

export interface AttemptBookingServiceItem {
    id: number;
    name: string;
    price?: number | string | null;
    duration_minutes?: number | null;
    description?: string | null;
    is_active: boolean;
}

export interface AttemptBookingScheduleItem {
    id: number;
    day_of_week: number;
    day_name: string;
    day_name_ar?: string;
    day_name_fr?: string;
    day_name_en?: string;
    start_time: string;
    end_time: string;
    slot_duration_minutes: number;
    is_active: boolean;
}

export interface AttemptBookingBookable {
    id: number;
    name: string;
    partner_type: string;
    is_center: boolean;
    title?: string;
    profession?: string | null;
    profession_code?: string | null;
    specialty?: string | null;
    speciality?: string | null;
    speciality_code?: string | null;
    custom_speciality?: string | null;
    address?: string | null;
    city?: string | null;
    location?: string | null;
    commune_id?: number | null;
    commune_code?: string | null;
    commune?: string | null;
    wilaya_code?: string | null;
    wilaya?: string | null;
    wilaya_name?: string | null;
    lat?: number | null;
    lng?: number | null;
    image_url?: string | null;
    avatar?: string | null;
    profile_pic?: string | null;
}

export interface AttemptBookingResponse {
    success: boolean;
    patient_info: {
        full_name: string;
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
        phone_number: string;
    };
    bookable: AttemptBookingBookable;
    services: AttemptBookingServiceItem[];
    schedules: AttemptBookingScheduleItem[];
}

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch a paginated list of all bookings for the authenticated patient.
 *
 * **HTTP Route:** `GET /api/v1/patient/bookings`
 *
 * @example
 * ```ts
 * const { bookings, current_page, total } = await getBookings({
 *   page: 1,
 *   per_page: 10,
 *   status_code: 'confirmed',
 * });
 * ```
 */
export async function getBookings(
    params?: GetBookingsParams,
): Promise<GetBookingsResponse> {
    const response = await api.get<GetBookingsResponse>('/patient/bookings', {
        params,
    });
    return response.data;
}

/**
 * Fetch detailed information for a specific booking by ID.
 *
 * **HTTP Route:** `GET /api/v1/patient/bookings/{id}`
 *
 * @example
 * ```ts
 * const { booking } = await getBooking(14);
 * console.log(booking.reference, booking.provider?.profession, booking.provider?.speciality);
 * ```
 */
export async function getBooking(
    id: number | string,
): Promise<GetBookingResponse> {
    const response = await api.get<GetBookingResponse>(
        `/patient/bookings/${id}`,
    );
    return response.data;
}

/**
 * Create a new appointment booking request.
 *
 * **HTTP Route:** `POST /api/v1/patient/bookings`
 *
 * @example
 * ```ts
 * const result = await createBooking({
 *   partner_id: 4,
 *   date: '2026-08-25',
 *   time: '10:30:00',
 *   service_id: 1,
 *   patient_name: 'Ahmed Benali',
 *   patient_phone: '0551111111',
 *   notes: 'Consultation générale',
 * });
 * ```
 */
export async function createBooking(
    payload: CreateBookingPayload,
): Promise<CreateBookingResponse> {
    const response = await api.post<CreateBookingResponse>(
        '/patient/bookings',
        payload,
    );
    return response.data;
}

/**
 * Update date/time or notes for a pending appointment.
 *
 * **HTTP Route:** `PUT /api/v1/patient/bookings/{id}`
 *
 * @example
 * ```ts
 * const result = await updateBooking(14, {
 *   booking_date: '2026-08-27',
 *   booking_time: '11:00',
 *   notes: 'Mis à jour de l’heure',
 * });
 * ```
 */
export async function updateBooking(
    id: number | string,
    payload: UpdateBookingPayload,
): Promise<UpdateBookingResponse> {
    const response = await api.put<UpdateBookingResponse>(
        `/patient/bookings/${id}`,
        payload,
    );
    return response.data;
}

/**
 * Fetch pre-filled booking options (patient info, bookable details with profession/speciality, partner services, schedules).
 *
 * **HTTP Route:** `GET /api/v1/patient/bookings/attempt?partner_id={id}`
 *
 * @example
 * ```ts
 * const attemptData = await attemptBooking(4);
 * console.log(attemptData.bookable.profession, attemptData.bookable.speciality);
 * console.log(attemptData.services, attemptData.schedules);
 * ```
 */
export async function attemptBooking(
    partnerId: number | string,
): Promise<AttemptBookingResponse> {
    const response = await api.get<AttemptBookingResponse>(
        '/patient/bookings/attempt',
        {
            params: { partner_id: partnerId },
        },
    );
    return response.data;
}

/**
 * Accept and confirm a proposed schedule change for a booking.
 *
 * **HTTP Route:** `POST /api/v1/patient/bookings/{id}/confirm-proposal`
 *
 * @example
 * ```ts
 * const result = await confirmProposal(14);
 * console.log(result.booking.status_code); // "confirmed"
 * ```
 */
export async function confirmProposal(
    id: number | string,
): Promise<CreateBookingResponse> {
    const response = await api.post<CreateBookingResponse>(
        `/patient/bookings/${id}/confirm-proposal`,
    );
    return response.data;
}
