// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

/** Supported target entity types for polymorphic likes. */
export type LikeableType =
    | 'professional'
    | 'doctor'
    | 'center'
    | 'pharmacy'
    | 'pharmacist'
    | 'patient'
    | string;

/** POST /api/v1/patient/toggle-like – Request body. */
export interface ToggleLikeRequest {
    /** Target model entity type (e.g. "professional", "center", "pharmacy"). */
    likeable_type: LikeableType;

    /** Primary key ID of the target entity. */
    likeable_id: number;
}

/** POST /api/v1/patient/toggle-like – Response body. */
export interface ToggleLikeResponse {
    /** Action status message (e.g. "Liked successfully." or "Unliked successfully."). */
    message: string;

    /** Whether the target entity is currently liked by the authenticated user. */
    is_liked: boolean;

    /** Total count of active likes for the target entity. */
    likes_count: number;

    /** Resolved likeable type string. */
    likeable_type: string;

    /** Target entity ID. */
    likeable_id: number;
}

// ─────────────────────────────────────────────
// API Call
// ─────────────────────────────────────────────

/**
 * Toggle like status for a target entity (Professional, Center, Pharmacy, etc.).
 *
 * **Endpoint:** `POST /api/v1/patient/toggle-like`
 *
 * @example
 * ```ts
 * const { is_liked, likes_count } = await toggleLike({
 *   likeable_type: 'professional',
 *   likeable_id: 5,
 * });
 * ```
 */
export async function toggleLike(
    data: ToggleLikeRequest,
): Promise<ToggleLikeResponse> {
    const response = await api.post<ToggleLikeResponse>(
        '/patient/toggle-like',
        data,
    );
    return response.data;
}
