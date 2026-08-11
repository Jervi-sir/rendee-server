// @ts-nocheck
import { api } from '@/utils/auth';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface PharmacyUser {
    id: number;
    name: string;
    full_name?: string | null;
    email: string;
    phone_number?: string | null;
}

export interface PharmacyServiceItem {
    id: number;
    name: string;
    description?: string;
    price?: number | string;
}

export interface PharmacyScheduleDay {
    day: string;
    isOpen: boolean;
    hours: string;
}

export interface PharmacySocialMediaLinks {
    whatsapp?: string | null;
    facebook?: string | null;
    tiktok?: string | null;
    instagram?: string | null;
    phone?: string | null;
}

export interface PatientPharmacy {
    id: number;
    name: string;
    location: string;
    bio: string;
    latitude: number | string | null;
    longitude: number | string | null;
    is_available: boolean;
    wilaya_code: string | null;
    user: PharmacyUser | null;
}

export interface PatientPharmacyDetailed extends PatientPharmacy {
    pharmacistName?: string;
    type?: string;
    phone?: string | null;
    address?: string | null;
    city?: string | null;
    is_24_7?: boolean;
    emergency_label?: string;
    weekly_schedule?: PharmacyScheduleDay[];
    social_media?: PharmacySocialMediaLinks;
    services?: PharmacyServiceItem[];
}

export interface GetPharmaciesParams {
    /** Filter by wilaya code */
    wilaya_code?: string;
    /** Search/Filter by city or address name */
    city?: string;
}

export interface GetPharmaciesResponse {
    pharmacies: PatientPharmacy[];
}

export interface GetPharmacyResponse {
    pharmacy: PatientPharmacyDetailed;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch a listing of available pharmacies formatted for patients with optional filters.
 *
 * **Endpoint:** `GET /patient/pharmacies`
 *
 * @example
 * ```ts
 * const { pharmacies } = await getPharmacies({
 *   wilaya_code: '31',
 *   city: 'Oran',
 * });
 * ```
 */
export async function getPharmacies(
    params?: GetPharmaciesParams,
): Promise<GetPharmaciesResponse> {
    const response = await api.get<GetPharmaciesResponse>(
        '/patient/pharmacies',
        {
            params,
        },
    );
    return response.data;
}

/**
 * Fetch detailed profile for a specific pharmacy viewed as patient.
 *
 * **Endpoint:** `GET /patient/pharmacies/{id}`
 *
 * @example
 * ```ts
 * const { pharmacy } = await getPharmacy(3);
 * ```
 */
export async function getPharmacy(
    id: number | string,
): Promise<GetPharmacyResponse> {
    const response = await api.get<GetPharmacyResponse>(
        `/patient/pharmacies/${id}`,
    );
    return response.data;
}
