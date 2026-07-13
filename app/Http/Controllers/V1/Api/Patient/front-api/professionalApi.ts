// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { ProfessionalsListResponse, ProfessionalDetailResponse } from './types';

export const professionalApi = {
    /**
     * Get list of professionals with optional filters.
     */
    getProfessionals: (params?: {
        profession_code?: string;
        speciality_code?: string;
        city?: string;
    }): Promise<AxiosResponse<ProfessionalsListResponse>> => {
        return apiClient.get('/patients/professionals', { params });
    },

    /**
     * Get a detailed profile of a specific professional.
     */
    getProfessionalDetail: (
        id: number,
    ): Promise<AxiosResponse<ProfessionalDetailResponse>> => {
        return apiClient.get(`/patients/professionals/${id}`);
    },
};

export default professionalApi;
