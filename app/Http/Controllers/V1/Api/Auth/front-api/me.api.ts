// @ts-nocheck
import { api } from '@/utils/api-client';
import { AuthUser } from './register.api';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface MeResponse {
    user: AuthUser;
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Fetch authenticated user profile.
 *
 * **Endpoint:** `GET /api/v1/auth/me`
 */
export async function getMe(): Promise<MeResponse> {
    const response = await api.get<MeResponse>('/auth/me');
    return response.data;
}
