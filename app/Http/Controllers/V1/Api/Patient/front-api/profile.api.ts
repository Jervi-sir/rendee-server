// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface PatientDetails {
    id?: number;
    date_of_birth: string | null;
    gender: 'male' | 'female' | string;
    address: string;
    city: string;
    medical_notes: string;
}

export interface PatientProfileUser {
    id: number;
    name: string;
    full_name: string;
    email: string;
    phone: string;
    profile_completed: boolean;
    image_url: string | null;
    patient: PatientDetails | null;
}

export interface GetProfileResponse {
    user: PatientProfileUser;
}

export interface UpdateProfilePayload {
    full_name: string;
    phone?: string;
    date_of_birth: string;
    gender: 'male' | 'female';
    address: string;
    city: string;
    medical_notes: string;
}

export interface UpdateProfileResponse {
    message: string;
    user: PatientProfileUser;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch current authenticated patient profile.
 *
 * **Endpoint:** `GET /patient/profile`
 *
 * @example
 * ```ts
 * const { user } = await getProfile();
 * ```
 */
export async function getProfile(): Promise<GetProfileResponse> {
    const response = await api.get<GetProfileResponse>('/patient/profile');
    return response.data;
}

/**
 * Update current patient profile details.
 *
 * **Endpoint:** `PUT /patient/profile`
 *
 * @example
 * ```ts
 * const result = await updateProfile({
 *   full_name: 'John Doe',
 *   phone: '0550000000',
 *   date_of_birth: '1995-05-15',
 *   gender: 'male',
 *   address: '123 Main St',
 *   city: 'Oran',
 *   medical_notes: 'No allergies',
 * });
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
