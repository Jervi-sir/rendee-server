// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { CentersListResponse, CenterDetailResponse } from './types';

export const centerApi = {
    /**
     * Get list of medical centers with optional filters.
     */
    getCenters: (params?: {
        center_catalog_code?: string;
        city?: string;
    }): Promise<AxiosResponse<CentersListResponse>> => {
        return apiClient.get('/patients/centers', { params });
    },

    /**
     * Get a detailed profile of a specific center.
     */
    getCenterDetail: (
        id: number,
    ): Promise<AxiosResponse<CenterDetailResponse>> => {
        return apiClient.get(`/patients/centers/${id}`);
    },
};

export default centerApi;
