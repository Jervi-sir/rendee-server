// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export type BookableType = 'professional' | 'center';

export interface BookingProviderSummary {
    id: number;
    name: string;
    title?: string;
    type?: string;
    specialty?: string | null;
    address?: string | null;
    city?: string | null;
    phone?: string | null;
    image_url?: string | null;
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
    bookable_type: BookableType;
    bookable_id: number;
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
    /** Target provider type */
    bookable_type: BookableType;
    /** ID of the target professional or center */
    bookable_id: number;
    /** Date string in YYYY-MM-DD format */
    date: string;
    /** Time string (e.g. '09:00' or '09:00:00') */
    time: string;
    /** Selected service ID */
    service_id: number;
    /** Full name of patient */
    patient_name: string;
    /** Contact phone number */
    patient_phone: string;
    /** Optional notes/symptoms for doctor */
    notes?: string;
}

export interface GetBookingsParams {
    /** Page number (default: 1) */
    page?: number;
    /** Items per page (default: 10) */
    per_page?: number;
    /** Filter by status code (optional) */
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

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch a paginated list of all bookings for the authenticated patient.
 *
 * **Endpoint:** `GET /patient/bookings`
 *
 * @example
 * ```ts
 * const { bookings, current_page, next_page } = await getBookings({
 *   page: 1,
 *   per_page: 10,
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
 * Fetch detailed profile for a specific booking.
 *
 * **Endpoint:** `GET /patient/bookings/{id}`
 *
 * @example
 * ```ts
 * const { booking } = await getBooking(14);
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
 * **Endpoint:** `POST /patient/bookings`
 *
 * @example
 * ```ts
 * const result = await createBooking({
 *   bookable_type: 'professional',
 *   bookable_id: 3,
 *   date: '2026-08-01',
 *   time: '10:00',
 *   service_id: 1,
 *   patient_name: 'John Doe',
 *   patient_phone: '0550000000',
 *   notes: 'General consultation',
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
