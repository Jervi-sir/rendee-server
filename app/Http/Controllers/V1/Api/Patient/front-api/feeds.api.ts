// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE PATIENT - FEED API CLIENT
 * ============================================================================
 * 
 * Provides typed access to the home discovery feed for patients:
 * - Healthcare provider discovery (Doctors, Dentists, Clinics, Pharmacies)
 * - Geolocation and distance-based sorting (near me)
 * - Filtering by wilaya, profession, speciality, and on-duty / 24/7 status
 * - Quick like status integration
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

export interface FeedLocation {
    wilaya_name: string;
    wilaya_number: string;
    address?: string | null;
    lat?: number | null;
    lng?: number | null;
}

export interface FeedPartnerType {
    code: string;
    label: string;
}

export interface FeedItem {
    id: number;
    name: string;
    profile_pic?: string | null;
    partner_type: FeedPartnerType;
    speciality: string;
    distance?: number | null;
    rating: number;
    reviews_count: number;
    is_liked: boolean;
    location: FeedLocation;
    is_on_duty: boolean;
}

export interface FeedFilterItem {
    key: string;
    label: string;
    count: number;
}

export interface FeedWilayaItem {
    code: string;
    number: string;
    en?: string;
    fr?: string;
    ar?: string;
}

export type FeedFilters = FeedFilterItem;
export type FeedWilayas = FeedWilayaItem;

export interface GetFeedParams {
    /** Search keyword (matches name, speciality, city, address, or bio) */
    query?: string;
    /** Filter by Wilaya code (e.g. "16", "31") */
    wilaya_code?: string;
    /** Alias for wilaya_code */
    wilaya?: string;
    /** Filter by partner type code (e.g. "doctor", "center", "dentist", "pharmacist") */
    partner_type?: string;
    /** Alias for partner_type */
    user_type?: string;
    /** Filter by profession code (e.g. "doctor", "dentist", "psychologist") */
    profession?: string;
    profession_code?: string;
    /** Filter by speciality code (e.g. "cardiology", "pediatrics") */
    speciality?: string;
    speciality_code?: string;
    /** Filter strictly for on-duty / emergency providers */
    on_duty?: boolean;
    /** User's current latitude for distance sorting */
    latitude?: number | string;
    /** User's current longitude for distance sorting */
    longitude?: number | string;
    /** Filter results within 50 km of user's coordinates */
    near_me?: boolean;
    /** Page number (default: 1) */
    page?: number;
    /** Items per page (default: 10) */
    per_page?: number;
}

export interface GetFeedResponse {
    results: FeedItem[];
    filters: FeedFilterItem[];
    wilayas: FeedWilayaItem[];
    current_page: number;
    next_page: number | null;
    total: number;
}

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch patient discovery feed with optional location, profession, speciality, and wilaya filters.
 *
 * **HTTP Route:** `GET /api/v1/patient/feed`
 *
 * @example
 * ```ts
 * const feed = await getFeed({
 *   latitude: 36.7538,
 *   longitude: 3.0588,
 *   profession: 'doctor',
 *   speciality: 'cardiology',
 *   near_me: true,
 *   page: 1,
 *   per_page: 10,
 * });
 * console.log(feed.results, feed.filters);
 * ```
 */
export async function getFeed(
    params?: GetFeedParams,
): Promise<GetFeedResponse> {
    const response = await api.get<GetFeedResponse>('/patient/feed', {
        params,
    });
    return response.data;
}

export default {
    getFeed,
};
