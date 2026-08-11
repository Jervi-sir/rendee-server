// @ts-nocheck
import { api } from '@/utils/api-client';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export type MapEntityType = 'professional' | 'center' | 'pharmacy';

export interface MapMarkerItem {
    id: number;
    title: string;
    latitude: number;
    longitude: number;
    entity_type: MapEntityType;
    entity_id: number;
    city?: string;
    address?: string;
}

export interface MapSelectedCard {
    title: string;
    subtitle: string;
    entity_type: MapEntityType;
    entity_id: number;
    latitude: number;
    longitude: number;
}

export interface GetMapDataParams {
    /** Filter markers by entity type ('professional', 'center', 'pharmacy', or 'all') */
    entity_type?: MapEntityType | 'all';
    /** Filter markers by wilaya code */
    wilaya_code?: string;
    /** Limit items per category (default: 10, max: 50) */
    limit?: number;
    /** User's current latitude */
    latitude?: number | string;
    /** User's current longitude */
    longitude?: number | string;
    /** Filter strictly near me */
    near_me?: boolean;
}


export interface GetMapDataResponse {
    markers: MapMarkerItem[];
    selected_card: MapSelectedCard | null;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch patient map markers and default selected card for healthcare entities (doctors, centers, pharmacies).
 *
 * **Endpoint:** `GET /patient/map`
 *
 * @example
 * ```ts
 * const { markers, selected_card } = await getMapData({
 *   latitude: 35.6971,
 *   longitude: -0.6308,
 * });
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
