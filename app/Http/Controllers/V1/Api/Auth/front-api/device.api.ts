// @ts-nocheck
import { api } from '@/utils/api-client';

export type DeviceType = 'ios' | 'android' | 'web';

export interface RegisterDeviceRequest {
    /** Unique hardware/device UUID. */
    device_id: string;
    /** Optional friendly device name (e.g. "iPhone 15 Pro"). */
    device_name?: string | null;
    /** Platform operating system. */
    device_type?: DeviceType | null;
    /** Device model name or string. */
    device_model?: string | null;
    /** Platform OS version string (e.g. "17.4"). */
    os_version?: string | null;
    /** React Native app version (e.g. "1.0.0"). */
    app_version?: string | null;
    /** FCM / APNS push token string. */
    push_notification_token?: string | null;
    /** Sandbox APNS push token (iOS dev). */
    push_notification_token_sandbox?: string | null;
    /** Enable / disable push notifications for device. */
    push_notifications_enabled?: boolean | null;
    /** Device locale/language code (defaults to "ar"). */
    language?: string | null;
    /** IANA timezone string (defaults to "Africa/Algiers"). */
    timezone?: string | null;
    /** Custom notification preference object/array. */
    notification_preferences?: Record<string, unknown> | null;
}

export interface UserDevice {
    id: number;
    user_id: number;
    device_id: string;
    device_name: string | null;
    device_type: DeviceType | null;
    device_model: string | null;
    os_version: string | null;
    app_version: string | null;
    push_notification_token: string | null;
    push_notification_token_sandbox: string | null;
    push_token_last_refreshed_at: string | null;
    push_notifications_enabled: boolean;
    language: string;
    timezone: string;
    notification_preferences: Record<string, unknown> | null;
    last_active_at: string | null;
    last_logged_in_at: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface RegisterDeviceResponse {
    message: string;
    device: UserDevice;
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Register or update device & push notification credentials for the authenticated user.
 *
 * **Endpoint:** `POST /api/v1/auth/devices`
 */
export async function registerDevice(
    data: RegisterDeviceRequest,
): Promise<RegisterDeviceResponse> {
    const response = await api.post<RegisterDeviceResponse>(
        '/v1/auth/devices',
        data,
    );
    return response.data;
}
