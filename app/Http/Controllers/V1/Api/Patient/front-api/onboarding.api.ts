// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE PATIENT - ONBOARDING API CLIENT
 * ============================================================================
 * 
 * Handles patient onboarding steps and medical profile setup:
 * - Checking onboarding completion status & percentage
 * - Submitting vital medical info (DOB, blood type, allergies, emergency contact)
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

export interface EmergencyContactItem {
    id?: string;
    name: string;
    relation?: string;
    relationship?: string;
    phone: string;
}

export interface PatientMedicalProfile {
    id: number;
    date_of_birth: string | null;
    gender: 'male' | 'female' | null;
    address: string | null;
    city: string | null;
    medical_notes: string | null;
    blood_type: string | null;
    allergies: string[];
    chronic_diseases: string[];
    medications: string[];
    emergency_contacts: EmergencyContactItem[];
}

export interface PatientOnboardingUser {
    id: number;
    name: string;
    full_name: string;
    email: string;
    phone: string;
    profile_completed: boolean;
    image_url: string | null;
    patient: PatientMedicalProfile | null;
}

export interface PatientOnboardingStatusResponse {
    success: boolean;
    is_completed: boolean;
    completion_percentage: number;
    user: PatientOnboardingUser;
}

export interface PatientOnboardingPayload {
    /** Date of birth in YYYY-MM-DD format */
    date_of_birth: string;
    /** Biological gender */
    gender: 'male' | 'female';
    /** Blood type (e.g. "O+", "A+", "B+", "AB-") */
    blood_type?: string;
    /** Primary contact phone */
    phone?: string;
    /** Emergency contact phone */
    emergency_phone?: string;
    /** Street address */
    address: string;
    /** City / Wilaya */
    city: string;
    /** Comma-separated allergies (e.g. "Pénicilline, Pollen") */
    allergies?: string;
    /** General medical notes */
    medical_notes?: string;
}

export interface PatientOnboardingResponse {
    success: boolean;
    message: string;
    is_completed: boolean;
    user: PatientOnboardingUser;
}

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch patient onboarding status and existing profile data.
 *
 * **HTTP Route:** `GET /api/v1/patient/onboarding`
 *
 * @example
 * ```ts
 * const status = await getPatientOnboarding();
 * console.log(status.is_completed, status.completion_percentage);
 * ```
 */
export async function getPatientOnboarding(): Promise<PatientOnboardingStatusResponse> {
    const response = await api.get<PatientOnboardingStatusResponse>('/patient/onboarding');
    return response.data;
}

/**
 * Submit or update patient onboarding details and medical profile.
 *
 * **HTTP Route:** `POST /api/v1/patient/onboarding`
 *
 * @example
 * ```ts
 * const result = await submitPatientOnboarding({
 *   date_of_birth: '1992-05-14',
 *   gender: 'male',
 *   blood_type: 'O+',
 *   address: '12 Rue Didouche Mourad',
 *   city: 'Alger',
 *   allergies: 'Pénicilline',
 * });
 * console.log(result.is_completed); // true
 * ```
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

export default {
    getPatientOnboarding,
    submitPatientOnboarding,
};
