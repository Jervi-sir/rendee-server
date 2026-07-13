// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { PharmacistServicesResponse } from './types';

export const serviceApi = {
    /**
     * Get services offered by the pharmacy.
     */
    getServices: (): Promise<AxiosResponse<PharmacistServicesResponse>> => {
        return apiClient.get('/pharmacists/services');
    },
};

export default serviceApi;
