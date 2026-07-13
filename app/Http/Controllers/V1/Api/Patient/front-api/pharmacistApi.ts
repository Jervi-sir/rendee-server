// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { PharmaciesListResponse, PharmacyDetailResponse } from './types';

export const pharmacistApi = {
    /**
     * Get list of pharmacies.
     */
    getPharmacies: (params?: {
        wilaya_code?: string;
        city?: string;
    }): Promise<AxiosResponse<PharmaciesListResponse>> => {
        return apiClient.get('/patients/pharmacies', { params });
    },

    /**
     * Get a detailed profile of a specific pharmacy.
     */
    getPharmacyDetail: (
        id: number,
    ): Promise<AxiosResponse<PharmacyDetailResponse>> => {
        return apiClient.get(`/patients/pharmacies/${id}`);
    },
};

export default pharmacistApi;
