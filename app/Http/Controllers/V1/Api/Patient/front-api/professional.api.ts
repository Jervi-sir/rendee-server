// @ts-nocheck
import { api } from '@/utils/auth';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface ProfessionalUser {
    id: number;
    name: string;
    full_name: string | null;
    email: string;
    phone_number: string | null;
    image_url: string | null;
}

export interface ProfessionalServiceItem {
    id: number;
    service_catalog_code?: string;
    name: string;
    price: number | string;
    duration_minutes: number;
}

export interface ProfessionalScheduleItem {
    id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    is_active: boolean;
}

export interface WeeklyScheduleDay {
    day: string;
    isOpen: boolean;
    hours: string;
}

export interface SocialMediaLinks {
    whatsapp?: string | null;
    facebook?: string | null;
    tiktok?: string | null;
    phone?: string | null;
}

export interface ProfessionalContactItem {
    id: number;
    user_id: number;
    type?: string;
    value?: string;
    [key: string]: any;
}

export interface PatientProfessional {
    id: number;
    user_id: number;
    name: string;
    title: string;
    specialty: string | null;
    speciality?: string | null;
    profession_code: string;
    professional_speciality_code: string;
    years_experience: number | null;
    bio: string | null;
    phone: string | null;
    address: string | null;
    city: string | null;
    wilaya_code: string | null;
    latitude: string | number | null;
    longitude: string | number | null;
    is_available: boolean;
    license_number: string | null;
    image_url: string | null;
    user: ProfessionalUser | null;
}

export interface PatientProfessionalDetailed extends PatientProfessional {
    services: ProfessionalServiceItem[];
    schedules: ProfessionalScheduleItem[];
    weekly_schedule?: WeeklyScheduleDay[];
    social_media?: SocialMediaLinks;
    contacts: ProfessionalContactItem[];
}

export interface GetProfessionalsParams {
    /** Filter by profession code */
    profession_code?: string;
    /** Filter by speciality code */
    speciality_code?: string;
    /** Filter by wilaya code */
    wilaya_code?: string;
    /** Search/Filter by city name */
    city?: string;
}

export interface GetProfessionalsResponse {
    professionals: PatientProfessional[];
}

export interface GetProfessionalResponse {
    professional: PatientProfessionalDetailed;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch a listing of healthcare professionals formatted for patients with optional filters.
 *
 * **Endpoint:** `GET /patient/professionals`
 *
 * @example
 * ```ts
 * const data = await getProfessionals({
 *   profession_code: 'DOCTOR',
 *   speciality_code: 'CARDIO',
 *   city: 'Oran',
 * });
 * ```
 */
export async function getProfessionals(
    params?: GetProfessionalsParams,
): Promise<GetProfessionalsResponse> {
    const response = await api.get<GetProfessionalsResponse>(
        '/patient/professionals',
        {
            params,
        },
    );
    return response.data;
}

/**
 * Fetch detailed profile for a specific professional viewed as patient.
 *
 * **Endpoint:** `GET /patient/professionals/{id}`
 *
 * @example
 * ```ts
 * const { professional } = await getProfessional(12);
 * ```
 */
export async function getProfessional(
    id: number | string,
): Promise<GetProfessionalResponse> {
    const response = await api.get<GetProfessionalResponse>(
        `/patient/professionals/${id}`,
    );
    return response.data;
}
