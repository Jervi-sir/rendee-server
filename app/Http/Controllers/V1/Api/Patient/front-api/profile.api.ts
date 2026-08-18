// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE PATIENT - PROFILE API CLIENT
 * ============================================================================
 * 
 * Provides typed access to patient personal information and medical records:
 * - Viewing patient profile, blood type, chronic conditions, and emergency contacts
 * - Updating medical history and personal contact information
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

export interface PatientEmergencyContact {
    id?: string;
    name: string;
    relation?: string;
    phone: string;
}

export interface PatientDetails {
    id?: number;
    date_of_birth: string | null;
    gender: 'male' | 'female' | string | null;
    address: string | null;
    city: string | null;
    medical_notes: string | null;
    blood_type: string | null;
    allergies: string[];
    chronic_diseases: string[];
    medications: string[];
    emergency_contacts: PatientEmergencyContact[];
}

export interface PatientProfileUser {
    id: number;
    name: string;
    full_name: string;
    email: string;
    phone: string;
    profile_completed: boolean;
    image_url: string | null;
    bookings_count?: number;
    searches_count?: number;
    files_count?: number;
    patient: PatientDetails | null;
}

export interface GetProfileResponse {
    user: PatientProfileUser;
}

export interface UpdateProfilePayload {
    full_name?: string;
    phone?: string;
    date_of_birth?: string;
    gender?: 'male' | 'female';
    address?: string;
    city?: string;
    medical_notes?: string;
    blood_type?: string;
    allergies?: string[];
    chronic_diseases?: string[];
    medications?: string[];
    emergency_contacts?: PatientEmergencyContact[];
}

export interface UpdateProfileResponse {
    message: string;
    user: PatientProfileUser;
}

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch current authenticated patient profile and medical information.
 *
 * **HTTP Route:** `GET /api/v1/patient/profile`
 *
 * @example
 * ```ts
 * const { user } = await getProfile();
 * console.log(user.full_name, user.patient?.blood_type);
 * ```
 */
export async function getProfile(): Promise<GetProfileResponse> {
    const response = await api.get<GetProfileResponse>('/patient/profile');
    return response.data;
}

/**
 * Update current patient profile details and medical history.
 *
 * **HTTP Route:** `PUT /api/v1/patient/profile`
 *
 * @example
 * ```ts
 * const result = await updateProfile({
 *   full_name: 'Ahmed Benali',
 *   phone: '0551111111',
 *   date_of_birth: '1992-05-14',
 *   gender: 'male',
 *   address: '12 Rue Didouche Mourad',
 *   city: 'Alger',
 *   blood_type: 'O+',
 *   allergies: ['Pénicilline'],
 * });
 * console.log(result.message);
 * ```
 */
export async function updateProfile(
    payload: UpdateProfilePayload,
): Promise<UpdateProfileResponse> {
    const response = await api.put<UpdateProfileResponse>(
        '/patient/profile',
        payload,
    );
    return response.data;
}

export default {
    getProfile,
    updateProfile,
};
