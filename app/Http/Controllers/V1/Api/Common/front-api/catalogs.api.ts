// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE CATALOGS API CLIENT
 * ============================================================================
 * 
 * Provides unified, typed access to all application reference catalogs:
 * - Professions & Specialities (Doctors, Dentists, Clinics, etc.)
 * - Wilayas (58 Algerian Provinces)
 * - Service Catalogs & Partner Services
 * - Contact Platforms (WhatsApp, Phone, Social Media)
 * - Booking Statuses & User Roles
 * - Registration Types
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

/**
 * Valid catalog resource keys for dynamic inclusion.
 */
export type CatalogInclude =
    | 'specialities'
    | 'professional_specialities' // Backward-compatible alias
    | 'professions'
    | 'partner_types'
    | 'center_catalogs'
    | 'service_catalogs'
    | 'partner_services'
    | 'professional_services'   // Backward-compatible alias
    | 'wilayas'
    | 'contact_platforms'
    | 'statuses'
    | 'user_roles'
    | 'registery_types'
    | 'all';

/**
 * Registration Type item (used in role selection during registration).
 * 
 * @example
 * ```json
 * {
 *   "code": "doctor",
 *   "en": "Doctor",
 *   "fr": "Médecin",
 *   "ar": "طبيب",
 *   "source": "partner_type"
 * }
 * ```
 */
export interface RegisteryTypeItem {
    code: string;
    en: string;
    fr: string;
    ar: string;
    source: 'user_role' | 'partner_type';
    created_at?: string;
    updated_at?: string;
}

/**
 * Medical or professional specialty classification.
 * 
 * @example
 * ```json
 * {
 *   "code": "cardiology",
 *   "profession_code": "doctor",
 *   "en": "Cardiology",
 *   "fr": "Cardiologie",
 *   "ar": "طب القلب"
 * }
 * ```
 */
export interface SpecialityItem {
    code: string;
    profession_code?: string | null;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Top-level profession category (Doctor, Dentist, Psychologist, etc.).
 * 
 * @example
 * ```json
 * {
 *   "code": "doctor",
 *   "en": "Doctor",
 *   "fr": "Médecin",
 *   "ar": "طبيب",
 *   "hex": "#0ea5e9",
 *   "specialities": [ ... ]
 * }
 * ```
 */
export interface ProfessionItem {
    code: string;
    en: string;
    fr: string;
    ar: string;
    hex?: string | null;
    specialities?: SpecialityItem[];
    created_at?: string;
    updated_at?: string;
}

/**
 * Partner account category type.
 * 
 * @example
 * ```json
 * {
 *   "code": "doctor",
 *   "en": "Doctor",
 *   "fr": "Médecin",
 *   "ar": "طبيب"
 * }
 * ```
 */
export interface PartnerTypeItem {
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Medical Center classification (Clinic, Laboratory, Radiology, etc.).
 * 
 * @example
 * ```json
 * {
 *   "code": "clinic",
 *   "en": "Private Clinic",
 *   "fr": "Clinique Privée",
 *   "ar": "عيادة خاصة"
 * }
 * ```
 */
export interface CenterCatalogItem {
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Supported contact platform (WhatsApp, Viber, Phone, etc.).
 * 
 * @example
 * ```json
 * {
 *   "code": "whatsapp",
 *   "en": "WhatsApp",
 *   "fr": "WhatsApp",
 *   "ar": "واتساب"
 * }
 * ```
 */
export interface ContactPlatform {
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Service catalog master item.
 * 
 * @example
 * ```json
 * {
 *   "code": "consultation",
 *   "source": "doctor",
 *   "en": "General Consultation",
 *   "fr": "Consultation Générale",
 *   "ar": "استشارة طبية"
 * }
 * ```
 */
export interface ServiceCatalogItem {
    code: string;
    source?: string | null;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Algerian Wilaya (Province).
 * 
 * @example
 * ```json
 * {
 *   "id": 16,
 *   "code": "16",
 *   "number": "16",
 *   "en": "Algiers",
 *   "fr": "Alger",
 *   "ar": "الجزائر"
 * }
 * ```
 */
export interface Wilaya {
    id: number;
    code: string;
    number?: number | string | null;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
    name_ar?: string | null;
    name_en?: string | null;
    created_at?: string;
    updated_at?: string;
}

/**
 * Booking status option.
 * 
 * @example
 * ```json
 * {
 *   "code": "confirmed",
 *   "en": "Confirmed",
 *   "fr": "Confirmé",
 *   "ar": "مؤكد"
 * }
 * ```
 */
export interface StatusItem {
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Application user role.
 * 
 * @example
 * ```json
 * {
 *   "code": "patient",
 *   "en": "Patient",
 *   "fr": "Patient",
 *   "ar": "مريض"
 * }
 * ```
 */
export interface UserRoleItem {
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

/**
 * Specific service offered by a partner with price & duration.
 * 
 * @example
 * ```json
 * {
 *   "id": 1,
 *   "partner_id": 4,
 *   "service_catalog_code": "consultation",
 *   "name": "Consultation Générale",
 *   "price": 2500,
 *   "duration_minutes": 30,
 *   "is_active": true
 * }
 * ```
 */
export interface PartnerServiceItem {
    id: number;
    partner_id: number;
    service_catalog_code?: string | null;
    name: string;
    description?: string | null;
    price?: number | string | null;
    duration_minutes?: number | null;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

/**
 * Parameters for the `getCatalogs` API call.
 */
export interface GetCatalogsParams {
    /** 
     * Array or comma-separated list of catalog resources to include.
     * Use `'all'` to fetch every catalog.
     * @example ['specialities', 'wilayas']
     */
    includes: CatalogInclude[] | string;

    /** 
     * Filter specialities by profession code.
     * @example "doctor" | "dentist" | "psychologist"
     */
    profession?: string;

    /** 
     * Filter service catalogs by source type.
     * @example "doctor" | "center" | "dentist"
     */
    source?: string;

    /** 
     * Filter partner services by specific partner ID.
     * @example 4
     */
    partner_id?: number;

    /** 
     * Whether to eager-load child specialities when requesting professions.
     * @example true
     */
    with_specialities?: boolean;
}

/**
 * Response payload containing requested catalog collections.
 */
export interface GetCatalogsResponse {
    specialities?: SpecialityItem[];
    professional_specialities?: SpecialityItem[];
    professions?: ProfessionItem[];
    partner_types?: PartnerTypeItem[];
    center_catalogs?: CenterCatalogItem[];
    service_catalogs?: ServiceCatalogItem[];
    partner_services?: PartnerServiceItem[];
    professional_services?: PartnerServiceItem[];
    wilayas?: Wilaya[];
    contact_platforms?: ContactPlatform[];
    statuses?: StatusItem[];
    user_roles?: UserRoleItem[];
    registery_types?: RegisteryTypeItem[];
}

// ─────────────────────────────────────────────
// API Methods & Helpers
// ─────────────────────────────────────────────

/**
 * Fetch reference catalog data with dynamic includes and filters.
 *
 * **HTTP Route:** `GET /api/v1/catalogs`
 *
 * @example
 * ```ts
 * // 1. Fetch specialities and wilayas for a doctor
 * const catalogs = await getCatalogs({
 *   includes: ['specialities', 'wilayas'],
 *   profession: 'doctor',
 * });
 * console.log(catalogs.specialities, catalogs.wilayas);
 *
 * // 2. Fetch all catalogs at once
 * const allData = await getCatalogs({ includes: 'all' });
 * ```
 */
export async function getCatalogs(
    params: GetCatalogsParams,
): Promise<GetCatalogsResponse> {
    const includesParam = Array.isArray(params.includes)
        ? params.includes.join(',')
        : params.includes;

    const response = await api.get<GetCatalogsResponse>('/catalogs', {
        params: {
            includes: includesParam,
            ...(params.profession ? { profession: params.profession } : {}),
            ...(params.source ? { source: params.source } : {}),
            ...(params.partner_id ? { partner_id: params.partner_id } : {}),
            ...(params.with_specialities ? { with_specialities: params.with_specialities } : {}),
        },
    });

    return response.data;
}

/**
 * Fetch medical and healthcare specialities, optionally filtered by parent profession.
 *
 * @param profession Optional profession code (e.g. `'doctor'`, `'dentist'`, `'psychologist'`)
 * @returns Array of SpecialityItem objects
 * 
 * @example
 * ```ts
 * const doctorSpecialties = await getSpecialities('doctor');
 * ```
 */
export async function getSpecialities(profession?: string): Promise<SpecialityItem[]> {
    const data = await getCatalogs({
        includes: ['specialities'],
        ...(profession ? { profession } : {}),
    });
    return data.specialities || [];
}

/**
 * Fetch all available profession categories (Doctor, Dentist, Psychologist, Nurse, etc.).
 *
 * @param withSpecialities If true, each profession includes nested `specialities`
 * @returns Array of ProfessionItem objects
 * 
 * @example
 * ```ts
 * const professions = await getProfessions(true);
 * professions.forEach(p => console.log(p.fr, p.specialities));
 * ```
 */
export async function getProfessions(withSpecialities = false): Promise<ProfessionItem[]> {
    const data = await getCatalogs({
        includes: ['professions'],
        with_specialities: withSpecialities,
    });
    return data.professions || [];
}

/**
 * Fetch all 58 Algerian Wilayas sorted by number/code.
 *
 * @returns Array of Wilaya objects
 * 
 * @example
 * ```ts
 * const wilayas = await getWilayas();
 * // [{ code: '16', number: '16', fr: 'Alger', ar: 'الجزائر' }, ...]
 * ```
 */
export async function getWilayas(): Promise<Wilaya[]> {
    const data = await getCatalogs({
        includes: ['wilayas'],
    });
    return data.wilayas || [];
}

/**
 * Fetch registration options (Patient role + Partner types) for user registration screens.
 *
 * @returns Array of RegisteryTypeItem objects
 */
export async function getRegisteryTypes(): Promise<RegisteryTypeItem[]> {
    const data = await getCatalogs({
        includes: ['registery_types'],
    });
    return data.registery_types || [];
}

/**
 * Extract a localized label from any multi-lingual catalog object with automatic fallbacks.
 *
 * @param item Catalog object with language fields (`ar`, `fr`, `en`)
 * @param lang Target language code (`'ar'` | `'fr'` | `'en'`), defaults to `'fr'`
 * @returns Localized label string
 * 
 * @example
 * ```ts
 * const label = getLocalizedCatalogLabel(speciality, 'ar'); // "طب القلب"
 * ```
 */
export function getLocalizedCatalogLabel(
    item?: { ar?: string | null; fr?: string | null; en?: string | null; label?: string | null } | null,
    lang: 'ar' | 'fr' | 'en' = 'fr',
): string {
    if (!item) return '';
    return item[lang] || item.fr || item.ar || item.en || item.label || '';
}
