// @ts-nocheck
import { api, AUTH_TOKEN_STORAGE_KEY, setApiToken } from '@/utils/api-client';

import AsyncStorage from '@react-native-async-storage/async-storage';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface LogoutResponse {
    message: string;
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Revoke the user's current access token and clear local session state.
 *
 * **Endpoint:** `POST /api/v1/auth/logout`
 */
export async function logout(): Promise<LogoutResponse> {
    try {
        const response = await api.post<LogoutResponse>('/auth/logout');
        return response.data;
    } finally {
        await AsyncStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
        setApiToken(null);
    }
}
