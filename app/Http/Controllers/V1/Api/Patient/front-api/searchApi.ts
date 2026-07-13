// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { SearchResponse } from './types';

export const searchApi = {
    /**
     * Perform global search across professionals, centers, and pharmacies.
     */
    search: (params?: {
        query?: string;
        speciality_id?: number;
    }): Promise<AxiosResponse<SearchResponse>> => {
        return apiClient.get('/patients/search', { params });
    },
};

export default searchApi;
