// @ts-nocheck
import { api } from '@/utils/auth';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface CenterUser {
    id: number;
    name: string;
    full_name: string | null;
    email: string;
    phone_number: string | null;
    image_url: string | null;
}

export interface CenterServiceItem {
    id: number;
    service_catalog_code?: string;
    name: string;
    price: number | string;
    duration_minutes: number;
}

export interface CenterWorkingHourItem {
    id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    is_active: boolean;
}

export interface CenterWeeklyScheduleDay {
    day: string;
    isOpen: boolean;
    hours: string;
}

export interface CenterSocialMediaLinks {
    whatsapp?: string | null;
    facebook?: string | null;
    tiktok?: string | null;
    phone?: string | null;
}

export interface CenterContactItem {
    id: number;
    user_id: number;
    type?: string;
    value?: string;
    [key: string]: any;
}

export interface PatientCenter {
    id: number;
    user_id: number;
    name: string;
    type: string;
    center_catalog_code: string;
    license_number: string | null;
    description: string | null;
    address: string | null;
    city: string | null;
    wilaya_code: string | null;
    phone: string | null;
    emergency_24_7: boolean;
    emergency_label: string;
    is_active: boolean;
    image_url: string | null;
    user: CenterUser | null;
}

export interface PatientCenterDetailed extends PatientCenter {
    services: CenterServiceItem[];
    working_hours_slots: CenterWorkingHourItem[];
    weekly_schedule?: CenterWeeklyScheduleDay[];
    social_media?: CenterSocialMediaLinks;
    contacts: CenterContactItem[];
}

export interface GetCentersParams {
    /** Filter by center catalog code (e.g. 'CLINIC', 'LABORATORY') */
    center_catalog_code?: string;
    /** Filter by wilaya code */
    wilaya_code?: string;
    /** Search/Filter by city name */
    city?: string;
}

export interface GetCentersResponse {
    centers: PatientCenter[];
}

export interface GetCenterResponse {
    center: PatientCenterDetailed;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch a listing of medical centers/laboratories formatted for patients with optional filters.
 *
 * **Endpoint:** `GET /patient/centers`
 *
 * @example
 * ```ts
 * const data = await getCenters({
 *   center_catalog_code: 'LABORATORY',
 *   city: 'Oran',
 * });
 * ```
 */
export async function getCenters(
    params?: GetCentersParams,
): Promise<GetCentersResponse> {
    const response = await api.get<GetCentersResponse>('/patient/centers', {
        params,
    });
    return response.data;
}

/**
 * Fetch detailed profile for a specific medical center viewed as patient.
 *
 * **Endpoint:** `GET /patient/centers/{id}`
 *
 * @example
 * ```ts
 * const { center } = await getCenter(5);
 * ```
 */
export async function getCenter(
    id: number | string,
): Promise<GetCenterResponse> {
    const response = await api.get<GetCenterResponse>(`/patient/centers/${id}`);
    return response.data;
}
