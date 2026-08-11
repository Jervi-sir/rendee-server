// @ts-nocheck
import { api, AUTH_TOKEN_STORAGE_KEY, setApiToken } from '@/utils/api-client';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthUser } from './register.api';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface LoginRequest {
    email: string;
    password: string;
    device_name?: string | null;
}

export interface LoginResponse {
    message: string;
    token_type: 'Bearer';
    access_token: string;
    user: AuthUser;
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Login user with credentials.
 *
 * **Endpoint:** `POST /api/v1/auth/login`
 *
 * On success, the access token is automatically stored in AsyncStorage
 * and set as default authorization header.
 */
export async function login(data: LoginRequest): Promise<LoginResponse> {
    const response = await api.post<LoginResponse>('/auth/login', data);
    const { access_token } = response.data;

    await AsyncStorage.setItem(AUTH_TOKEN_STORAGE_KEY, access_token);
    setApiToken(access_token);

    return response.data;
}
