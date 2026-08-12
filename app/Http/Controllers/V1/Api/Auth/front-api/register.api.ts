// @ts-nocheck
import { api, AUTH_TOKEN_STORAGE_KEY, setApiToken } from '@/utils/api-client';
import AsyncStorage from '@react-native-async-storage/async-storage';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

/** Accepted role codes for registration. */
export type UserRoleCode = 'patient' | 'partner';

/** POST /api/v1/auth/register – request body. */
export interface RegisterRequest {
    /** Required – the user's role. */
    user_role_code: UserRoleCode;

    /** Optional – partner type code when user_role_code is partner. */
    partner_type?: string | null;

    /** Optional – short display name. */
    name?: string | null;

    /** Optional – full name. */
    full_name?: string | null;

    /** Required – email address (must be unique). */
    email: string;

    /** Optional – phone number (max 50 chars). */
    phone_number?: string | null;

    /** Required – password (min 8 chars). */
    password: string;

    /** Required – must match `password`. */
    password_confirmation: string;

    /** Optional – whether the profile is already complete. */
    profile_completed?: boolean;
}

/** The user object returned after registration. */
export interface AuthUser {
    id: number;
    user_role_code: string;
    name: string;
    full_name: string | null;
    email: string;
    phone_number: string | null;
    profile_completed: boolean;
    [key: string]: unknown;
}

/** POST /api/v1/auth/register – response body. */
export interface RegisterResponse {
    message: string;
    token_type: 'Bearer';
    access_token: string;
    user: AuthUser;
}

/** Validation error shape returned by Laravel (422). */
export interface RegisterValidationError {
    message: string;
    errors: Partial<Record<keyof RegisterRequest, string[]>>;
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Register a new user account.
 *
 * **Endpoint:** `POST /api/v1/auth/register`
 *
 * On success the token is automatically persisted to AsyncStorage
 * and set on the axios instance for subsequent requests.
 *
 * @example
 * ```ts
 * const { user, access_token } = await register({
 *   user_role_code: 'patient',
 *   email: 'ali@example.com',
 *   password: 'secret123',
 *   password_confirmation: 'secret123',
 * });
 * ```
 */
export async function register(
    data: RegisterRequest,
): Promise<RegisterResponse> {
    const response = await api.post<RegisterResponse>('/auth/register', data);

    const { access_token } = response.data;

    // Persist token & set default header for future requests
    await AsyncStorage.setItem(AUTH_TOKEN_STORAGE_KEY, access_token);
    setApiToken(access_token);

    return response.data;
}
