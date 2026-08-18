// @ts-nocheck
import { api } from '@/utils/auth';

/**
 * ============================================================================
 * RENDEE PATIENT - MAP API CLIENT
 * ============================================================================
 * 
 * Provides typed access to patient map search and geo-markers for healthcare entities.
 */

// ─────────────────────────────────────────────
// Types & Interfaces
// ─────────────────────────────────────────────

export interface PartnerMarkerType {
    code: string;
    label: string;
}

export interface PartnerMarker {
    id: number;
    lat: number;
    lng: number;
    title: string;
    address: string;
    partner_type: PartnerMarkerType;
    pin_color: string;
}

export interface MapFilterOption {
    key: string;
    label: string;
    count: number;
}

export interface MapWilayaOption {
    code: string;
    number?: string | number;
    en?: string;
    fr?: string;
    ar?: string;
    lat?: number;
    lng?: number;
}

export interface GetMapDataParams {
    /** Filter markers by partner type ('doctor', 'center', 'pharmacy', 'dentist', or 'all') */
    partner_type?: string;
    /** Alias for partner_type */
    user_type?: string;
    /** Alias for partner_type */
    entity_type?: string;
    /** Filter markers by wilaya code */
    wilaya_code?: string;
    /** Alias for wilaya_code */
    wilaya?: string;
    /** Search query */
    query?: string;
}

export interface GetMapDataResponse {
    markers: PartnerMarker[];
    filters: MapFilterOption[];
    wilayas: MapWilayaOption[];
}

// ─────────────────────────────────────────────
// API Methods
// ─────────────────────────────────────────────

/**
 * Fetch patient map markers, category filters, and active wilayas.
 *
 * **HTTP Route:** `GET /api/v1/patient/map`
 *
 * @example
 * ```ts
 * const { markers, filters, wilayas } = await getMapData({
 *   partner_type: 'doctor',
 *   wilaya_code: '16',
 * });
 * console.log(markers);
 * ```
 */
export async function getMapData(
    params?: GetMapDataParams,
): Promise<GetMapDataResponse> {
    const response = await api.get<GetMapDataResponse>('/patient/map', {
        params,
    });
    return response.data;
}

export default {
    getMapData,
};
