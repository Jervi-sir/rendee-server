// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface ChangePasswordRequest {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
}

export interface ChangePasswordResponse {
    success: boolean;
    message: string;
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Change authenticated user password.
 *
 * **Endpoint:** `POST /api/v1/auth/change-password`
 * **Auth:** Requires Bearer token (auth:sanctum)
 *
 * @param data Password update payload
 * @returns Status message indicating password change result
 */
export async function changePassword(
    data: ChangePasswordRequest,
): Promise<ChangePasswordResponse> {
    const response = await api.post<ChangePasswordResponse>(
        '/auth/change-password',
        data,
    );
    return response.data;
}
