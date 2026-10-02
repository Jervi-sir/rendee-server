import { api } from "@/utils/auth";

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface ProfessionalBookingTab {
  key: string;
  label: string;
  count: number;
}

export interface ProfessionalBookingItem {
  id: number;
  reference: string;
  patient_name: string;
  date: string;
  time: string;
  visit_type: string;
  price?: number | string | null;
  status: string;
  status_key: string;
  proposed_date?: string | null;
  proposed_time?: string | null;
  has_pending_proposal: boolean;
  can_confirm: boolean;
  can_reject: boolean;
  can_cancel: boolean;
  can_complete: boolean;
  can_suggest_new_time: boolean;
  can_follow_up: boolean;
}

export interface ProfessionalBookingDetail {
  id: number;
  patient_id?: number | null;
  reference: string;
  patient_name: string;
  patient_phone: string;
  patient_email: string;
  date: string;
  time: string;
  visit_type: string;
  service_name: string;
  price?: number | string | null;
  status: "pending" | "confirmed" | "completed" | "cancelled" | "no_show" | string;
  status_label: string;
  status_key: string;
  proposed_date?: string | null;
  proposed_time?: string | null;
  has_pending_proposal: boolean;
  can_confirm: boolean;
  can_reject: boolean;
  can_cancel: boolean;
  can_complete: boolean;
  can_suggest_new_time: boolean;
  can_follow_up: boolean;
  notes?: string | null;
}

export interface GetProfessionalBookingsParams {
  /** Tab key filter: "pending" | "confirmed" | "previous" | "all" */
  tab?: "pending" | "confirmed" | "previous" | "all" | string;
}

export interface GetProfessionalBookingsResponse {
  tabs: ProfessionalBookingTab[];
  patients_count?: number;
  appointments: ProfessionalBookingItem[];
}

export interface GetProfessionalBookingByIdResponse {
  success: boolean;
  appointment: ProfessionalBookingDetail;
}

export interface UpdateProfessionalBookingParams {
  status: "confirmed" | "completed" | "cancelled" | "rejected" | "no_show";
  notes?: string;
}

export interface CompleteProfessionalBookingParams {
  notes?: string;
}

export interface CancelProfessionalBookingParams {
  reason?: string;
  notes?: string;
}

export interface SuggestProfessionalBookingParams {
  proposed_date: string; // YYYY-MM-DD
  proposed_time: string; // HH:mm
  notes?: string;
}

export interface CreateFollowUpBookingParams {
  booking_date: string; // YYYY-MM-DD
  booking_time: string; // HH:mm
  service_id?: number;
  notes?: string;
  status?: "confirmed" | "pending";
}

export interface BookingActionResponse {
  success: boolean;
  message?: string;
  booking: any;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch list of appointments for the authenticated partner grouped by tabs.
 *
 * **Endpoint:** `GET /partner/bookings`
 */
export async function getProfessionalBookings(
  params?: GetProfessionalBookingsParams,
): Promise<GetProfessionalBookingsResponse> {
  const response = await api.get<GetProfessionalBookingsResponse>(
    "/partner/bookings",
    {
      params,
    },
  );
  return response.data;
}

/**
 * Fetch a single appointment details by ID.
 *
 * **Endpoint:** `GET /partner/bookings/{id}`
 */
export async function getProfessionalBookingById(
  id: number,
): Promise<GetProfessionalBookingByIdResponse> {
  const response = await api.get<GetProfessionalBookingByIdResponse>(
    `/partner/bookings/${id}`,
  );
  return response.data;
}

/**
 * Update appointment status (confirm, complete, cancel, reject).
 *
 * **Endpoint:** `PUT /partner/bookings/{id}`
 */
export async function updateProfessionalBookingStatus(
  id: number,
  params: UpdateProfessionalBookingParams,
): Promise<BookingActionResponse> {
  const response = await api.put<BookingActionResponse>(
    `/partner/bookings/${id}`,
    params,
  );
  return response.data;
}

/**
 * Mark a confirmed appointment as completed.
 *
 * **Endpoint:** `POST /partner/bookings/{id}/complete`
 */
export async function completeProfessionalBooking(
  id: number,
  params?: CompleteProfessionalBookingParams,
): Promise<BookingActionResponse> {
  const response = await api.post<BookingActionResponse>(
    `/partner/bookings/${id}/complete`,
    params || {},
  );
  return response.data;
}

/**
 * Cancel an appointment (pending or confirmed).
 *
 * **Endpoint:** `POST /partner/bookings/{id}/cancel`
 */
export async function cancelProfessionalBooking(
  id: number,
  params?: CancelProfessionalBookingParams,
): Promise<BookingActionResponse> {
  const response = await api.post<BookingActionResponse>(
    `/partner/bookings/${id}/cancel`,
    params || {},
  );
  return response.data;
}

/**
 * Propose an alternative date and time for an appointment.
 *
 * **Endpoint:** `POST /partner/bookings/{id}/suggest`
 */
export async function suggestProfessionalBookingTime(
  id: number,
  params: SuggestProfessionalBookingParams,
): Promise<BookingActionResponse> {
  const response = await api.post<BookingActionResponse>(
    `/partner/bookings/${id}/suggest`,
    params,
  );
  return response.data;
}

/**
 * Create a second follow-up booking for the same patient.
 *
 * **Endpoint:** `POST /partner/bookings/{id}/follow-up`
 */
export async function createFollowUpBooking(
  id: number,
  params: CreateFollowUpBookingParams,
): Promise<BookingActionResponse> {
  const response = await api.post<BookingActionResponse>(
    `/partner/bookings/${id}/follow-up`,
    params,
  );
  return response.data;
}
