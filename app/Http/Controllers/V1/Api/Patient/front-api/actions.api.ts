// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

/** POST /api/v1/patient/toggle-like – Request body. */
export interface ToggleLikeRequest {
    /** Target partner ID to like/unlike. */
    partner_id?: number;
    /** Alias for partner_id. */
    id?: number;
}

/** POST /api/v1/patient/toggle-like – Response body. */
export interface ToggleLikeResponse {
    /** Action status message ("Liked successfully." or "Unliked successfully."). */
    message: string;

    /** Whether the target partner is currently liked by the authenticated user. */
    is_liked: boolean;

    /** Total count of active likes for the partner. */
    likes_count: number;

    /** Target partner ID. */
    partner_id: number;
}

// ─────────────────────────────────────────────
// API Call
// ─────────────────────────────────────────────

/**
 * Toggle like status for a partner.
 *
 * **Endpoint:** `POST /api/v1/patient/toggle-like`
 *
 * @example
 * ```ts
 * const { is_liked, likes_count } = await toggleLike({
 *   partner_id: 5,
 * });
 * ```
 */
export async function toggleLike(
    data: ToggleLikeRequest,
): Promise<ToggleLikeResponse> {
    const payload = {
        partner_id: data.partner_id ?? data.id,
    };

    const response = await api.post<ToggleLikeResponse>(
        '/patient/toggle-like',
        payload,
    );
    return response.data;
}
