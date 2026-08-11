// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export type CatalogInclude =
    | 'professional_specialities'
    | 'contact_platforms'
    | 'service_catalogs'
    | 'wilayas'
    | 'statuses'
    | 'user_roles'
    | 'professional_services';

export interface ProfessionalSpeciality {
    id: number;
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

export interface ContactPlatform {
    id: number;
    code: string;
    name: string;
    icon?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface ServiceCatalogItem {
    id: number;
    code: string;
    source?: string | null;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

export interface Wilaya {
    id: number;
    code: string;
    name_ar: string;
    name_en?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface StatusItem {
    id: number;
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

export interface UserRoleItem {
    id: number;
    code: string;
    en: string;
    fr: string;
    ar: string;
    created_at?: string;
    updated_at?: string;
}

export interface ProfessionalServiceItem {
    id: number;
    professional_id: number;
    service_catalog_code: string;
    price: string | number;
    duration_minutes: number;
    created_at?: string;
    updated_at?: string;
}

export interface GetCatalogsParams {
    /** Array or comma-separated list of catalog resources to include. */
    includes: CatalogInclude[] | string;
    /** Optional source filter (applies only to `service_catalogs`, e.g. "doctor", "center"). */
    source?: string;
}

export interface GetCatalogsResponse {
    professional_specialities?: ProfessionalSpeciality[];
    contact_platforms?: ContactPlatform[];
    service_catalogs?: ServiceCatalogItem[];
    wilayas?: Wilaya[];
    statuses?: StatusItem[];
    user_roles?: UserRoleItem[];
    professional_services?: ProfessionalServiceItem[];
}

// ─────────────────────────────────────────────
// API call
// ─────────────────────────────────────────────

/**
 * Get catalog reference data with dynamic includes.
 *
 * **Endpoint:** `GET /catalogs?includes=professional_specialities,wilayas&source=doctor`
 *
 * @example
 * ```ts
 * const data = await getCatalogs({
 *   includes: ['professional_specialities', 'wilayas', 'service_catalogs'],
 *   source: 'doctor',
 * });
 * ```
 */
export async function getCatalogs(
    params: GetCatalogsParams,
): Promise<GetCatalogsResponse> {
    const includesParam = Array.isArray(params.includes)
        ? params.includes.join(',')
        : params.includes;

    const response = await api.get<GetCatalogsResponse>('/catalogs', {
        params: {
            includes: includesParam,
            ...(params.source ? { source: params.source } : {}),
        },
    });

    return response.data;
}
