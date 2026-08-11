// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export type FeedUserType = 'professional' | 'center' | 'pharmacy';
export type FeedCategoryType = 'doctor' | 'center' | 'pharmacy';

export interface FeedLocation {
    wilaya_name?: string | null;
    wilaya_number?: number | null;
    address?: string | null;
}

export interface FeedItem {
    id: number;
    name?: string;
    fullname: string;
    type?: FeedCategoryType;
    user_type: FeedUserType;
    typeLabel?: string;
    type_label?: string;
    specialty?: string;
    speciality: string;
    distance: number | null;
    distance_text?: string | null;
    rating?: number;
    reviews_count?: number;
    isOnDuty?: boolean;
    is_on_duty?: boolean;
    is_liked?: boolean;
    is_favorite?: boolean;
    wilaya?: string | null;
    address?: string | null;
    location: FeedLocation;
    images: string[];
    created_at?: string | null;
}

export interface GetFeedParams {
    /** Optional search query (matches name, speciality, bio, or description). */
    query?: string;
    /** Filter by Wilaya name. */
    wilaya?: string;
    /** Filter by user/category type. */
    user_type?: string;
    /** Filter strictly for items on duty. */
    on_duty?: boolean;
    /** Filter items with rating >= min_rating. */
    min_rating?: number;
    /** User's current latitude for distance calculation. */
    latitude?: number | string;
    /** User's current longitude for distance calculation. */
    longitude?: number | string;
    /** Filter results strictly to items near the user with valid location. */
    near_me?: boolean;
    /** Page number (default: 1). */
    page?: number;
    /** Items per page (default: 10). */
    per_page?: number;
}

export interface GetFeedResponse {
    results: FeedItem[];
    current_page: number;
    next_page: number | null;
    total: number;
}

// ─────────────────────────────────────────────
// API Call
// ─────────────────────────────────────────────

/**
 * Fetch patient suggestions feed listing healthcare professionals, centers, and pharmacies.
 *
 * **Endpoint:** `GET /patient/feed`
 *
 * @example
 * ```ts
 * const feedData = await getFeed({
 *   latitude: 35.6971,
 *   longitude: -0.6308,
 *   near_me: true,
 *   page: 1,
 *   per_page: 10,
 * });
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
