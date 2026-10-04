// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export type SupportedLocale = 'ar' | 'fr' | 'en';
export type TermsAudience = 'patient' | 'partner' | 'actions' | 'all';
export type ActionTarget = 'medical_record' | 'patient_registration' | 'partner_onboarding' | 'all';

export interface ConsentActionItem {
    id: string;
    type: 'toggle' | 'checkbox';
    required: boolean;
    label: string;
    description?: string;
    default_value: boolean;
}

export interface ConsentActionGroup {
    id: string;
    title: string;
    subtitle?: string;
    intro?: string;
    items: ConsentActionItem[];
    notice?: string;
}

export interface TermsSection {
    id: string;
    title: string;
    content: string;
    items?: string[];
}

export interface TermsDocument {
    title: string;
    version: string;
    summary?: string;
    sections: TermsSection[];
}

export interface TermsResponse {
    audience: 'patient' | 'partner';
    locale: SupportedLocale;
    available_locales: SupportedLocale[];
    last_updated: string;
    effective_date: string;
    terms: TermsDocument;
    privacy_policy: TermsDocument;
    actions?: ConsentActionGroup[];
}

export interface ActionGroupResponse {
    locale: SupportedLocale;
    available_locales: SupportedLocale[];
    target: string;
    data?: ConsentActionGroup;
    groups?: ConsentActionGroup[];
}

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch patient terms of service, privacy policy, and medical consent toggle actions.
 *
 * **Endpoint:** `GET /api/v1/terms/patient`
 */
export async function getPatientTerms(locale: SupportedLocale = 'ar'): Promise<TermsResponse> {
    const response = await api.get<TermsResponse>('/terms/patient', {
        params: { lang: locale },
    });
    return response.data;
}

/**
 * Fetch partner terms of service, privacy policy, and onboarding consent toggle actions.
 *
 * **Endpoint:** `GET /api/v1/terms/partner`
 */
export async function getPartnerTerms(locale: SupportedLocale = 'ar'): Promise<TermsResponse> {
    const response = await api.get<TermsResponse>('/terms/partner', {
        params: { lang: locale },
    });
    return response.data;
}

/**
 * Fetch toggleable actions/consents for frontend component interception.
 *
 * **Endpoint:** `GET /api/v1/terms/actions`
 *
 * Example: `getTermsActions('medical_record', 'fr')`
 */
export async function getTermsActions(
    target?: ActionTarget,
    locale: SupportedLocale = 'ar'
): Promise<ActionGroupResponse> {
    const response = await api.get<ActionGroupResponse>('/terms/actions', {
        params: {
            target: target || undefined,
            lang: locale,
        },
    });
    return response.data;
}

/**
 * General terms query endpoint.
 *
 * **Endpoint:** `GET /api/v1/terms`
 */
export async function getTerms(params?: {
    audience?: TermsAudience;
    lang?: SupportedLocale;
    with_actions?: boolean;
}): Promise<TermsResponse | Record<string, any>> {
    const response = await api.get('/terms', {
        params,
    });
    return response.data;
}
