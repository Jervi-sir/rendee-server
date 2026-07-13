// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    WilayasResponse,
    ContactPlatformsResponse,
    ServicesResponse,
} from './types';

export const catalogApi = {
    /**
     * Get list of all Wilayas.
     */
    getWilayas: (): Promise<AxiosResponse<WilayasResponse>> => {
        return apiClient.get('/common/wilayas');
    },

    /**
     * Get all contact platform types.
     */
    getContactPlatforms: (): Promise<
        AxiosResponse<ContactPlatformsResponse>
    > => {
        return apiClient.get('/common/contact-platforms');
    },

    /**
     * Get service catalogs with optional filter by source.
     */
    getServices: (params?: {
        source?: string;
    }): Promise<AxiosResponse<ServicesResponse>> => {
        return apiClient.get('/common/services', { params });
    },
};

export default catalogApi;
