// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE PATIENT - PARTNER PROFILE API CLIENT
 * ============================================================================
 * 
 * Provides typed access to the public profile of healthcare partners (Doctors,
 * Clinics, Pharmacies, Dentists, etc.) viewed by patients.
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

/**
 * Contact item for a partner (WhatsApp, Phone, Viber, etc.).
 * 
 * @example
 * ```json
 * {
 *   "platform": "whatsapp",
 *   "value": "0552222222"
 * }
 * ```
 */
export interface PartnerContact {
    platform: string;
    value: string;
}

/**
 * Medical or clinical service offered by the partner.
 * 
 * @example
 * ```json
 * {
 *   "id": 1,
 *   "title": "Consultation Générale",
 *   "label": "Service médical de Consultation Générale",
 *   "price": "2000"
 * }
 * ```
 */
export interface PartnerService {
    id?: number;
    title: string;
    label: string;
    price: string;
}

/**
 * Daily schedule entry for the partner.
 * 
 * @example
 * ```json
 * {
 *   "day": "الأحد",
 *   "hour_range": "08:30 - 17:00",
 *   "is_open": true
 * }
 * ```
 */
export interface PartnerScheduleDay {
    day: string;
    hour_range: string;
    is_open: boolean;
}

/**
 * Professional certification or diploma.
 * 
 * @example
 * ```json
 * {
 *   "name": "شهادة الاعتماد الطبي",
 *   "type": "طبي"
 * }
 * ```
 */
export interface PartnerCertificate {
    name: string;
    type: string;
}

/**
 * Geographic coordinates and address for the partner.
 * 
 * @example
 * ```json
 * {
 *   "label": "12 Rue Didouche Mourad, Alger, الجزائر",
 *   "lat": 36.7538,
 *   "lng": 3.0588
 * }
 * ```
 */
export interface PartnerLocation {
    label: string;
    lat: number;
    lng: number;
}

/**
 * Partner account category type info.
 * 
 * @example
 * ```json
 * {
 *   "code": "doctor",
 *   "label": "طبيب / أخصائي"
 * }
 * ```
 */
export interface PartnerTypeInfo {
    code: string;
    label: string;
}

/**
 * Profession classification info.
 * 
 * @example
 * ```json
 * {
 *   "code": "doctor",
 *   "label": "Médecin",
 *   "hex": "#0ea5e9"
 * }
 * ```
 */
export interface ProfessionInfo {
    code: string;
    label: string;
    hex?: string | null;
}

/**
 * Speciality classification info.
 * 
 * @example
 * ```json
 * {
 *   "code": "cardiology",
 *   "label": "Cardiologie"
 * }
 * ```
 */
export interface SpecialityInfo {
    code: string;
    label: string;
}

/**
 * Full Partner Profile payload returned by `GET /api/v1/patient/partners/{id}`.
 */
export interface PartnerProfile {
    id: number;
    name: string;
    bio: string;
    phone_numbers: string[];
    location: PartnerLocation;
    partner_type: PartnerTypeInfo;
    profession?: ProfessionInfo;
    speciality?: SpecialityInfo;
    contacts: PartnerContact[];
    services: PartnerService[];
    scheduel: PartnerScheduleDay[];
    certificates: PartnerCertificate[];
}

/**
 * Type alias for backward compatibility with existing screen components.
 */
export type partnerType = PartnerProfile;

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch the public detailed profile of a healthcare partner by ID.
 *
 * **HTTP Route:** `GET /api/v1/patient/partners/{id}`
 *
 * @param id The partner ID
 * @returns Promise resolving to the complete PartnerProfile object
 *
 * @example
 * ```ts
 * import { getPartnerDetails } from '@/apis/patients/partner.api';
 *
 * const partner = await getPartnerDetails(4);
 * console.log(partner.name);            // "Dr. Karim Amrani"
 * console.log(partner.services);        // [{ title: "Consultation Générale", price: "2000" }]
 * console.log(partner.location.label);  // "12 Rue Didouche Mourad, Alger"
 * ```
 */
export async function getPartnerDetails(id: number | string): Promise<PartnerProfile> {
    const res = await api.get<PartnerProfile>(`/patient/partners/${id}`);
    return res.data;
}

export default {
    getPartnerDetails,
};
