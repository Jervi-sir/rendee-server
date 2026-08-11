// @ts-nocheck
import { api } from '@/utils/api-client';
import { PatientProfileUser } from './profile.api';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface PatientOnboardingStatusResponse {
    success: boolean;
    is_completed: boolean;
    completion_percentage: number;
    user: PatientProfileUser;
}

export interface PatientOnboardingPayload {
    date_of_birth: string;
    gender: 'male' | 'female';
    blood_type?: string;
    phone?: string;
    emergency_phone?: string;
    address: string;
    city: string;
    allergies?: string;
    medical_notes?: string;
}

export interface PatientOnboardingResponse {
    success: boolean;
    message: string;
    is_completed: boolean;
    user: PatientProfileUser;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch patient onboarding status and existing profile info.
 *
 * **Endpoint:** `GET /patient/onboarding`
 */
export async function getPatientOnboarding(): Promise<PatientOnboardingStatusResponse> {
    const response = await api.get<PatientOnboardingStatusResponse>('/patient/onboarding');
    return response.data;
}

/**
 * Submit or update patient onboarding details.
 *
 * **Endpoint:** `POST /patient/onboarding`
 */
export async function submitPatientOnboarding(
    payload: PatientOnboardingPayload,
): Promise<PatientOnboardingResponse> {
    const response = await api.post<PatientOnboardingResponse>(
        '/patient/onboarding',
        payload,
    );
    return response.data;
}
