// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface SendSupportMessageRequest {
    subject?: string;
    message: string;
    name?: string;
    email?: string;
    phone?: string;
}

export interface SendSupportMessageResponse {
    success: boolean;
    message: string;
    data: {
        id: number;
        subject: string | null;
        status: string;
        created_at: string;
    };
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Send support inquiry / message.
 *
 * **Endpoint:** `POST /api/v1/support-messages`
 *
 * Can be called authenticated (automatically associates user) or unauthenticated.
 */
export async function sendSupportMessage(
    data: SendSupportMessageRequest
): Promise<SendSupportMessageResponse> {
    const response = await api.post<SendSupportMessageResponse>(
        '/support-messages',
        data
    );
    return response.data;
}
