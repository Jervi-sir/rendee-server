// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    ProfessionalServicesResponse,
    ProfessionalServiceCatalogResponse,
    ProfessionalServiceStoreInput,
    ProfessionalServiceStoreResponse,
    ProfessionalServiceUpdateInput,
    ProfessionalServiceUpdateResponse,
    ProfessionalServiceDeleteResponse,
} from './types';

export const serviceApi = {
    /**
     * Get list of services offered by the professional.
     */
    getServices: (): Promise<AxiosResponse<ProfessionalServicesResponse>> => {
        return apiClient.get('/professionals/services');
    },

    /**
     * Get the list of global service catalogs that can be added.
     */
    getCatalog: (): Promise<
        AxiosResponse<ProfessionalServiceCatalogResponse>
    > => {
        return apiClient.get('/professionals/services/catalog');
    },

    /**
     * Add a service to the professional's profile.
     */
    addService: (
        data: ProfessionalServiceStoreInput,
    ): Promise<AxiosResponse<ProfessionalServiceStoreResponse>> => {
        return apiClient.post('/professionals/services', data);
    },

    /**
     * Update an existing service.
     */
    updateService: (
        id: number,
        data: ProfessionalServiceUpdateInput,
    ): Promise<AxiosResponse<ProfessionalServiceUpdateResponse>> => {
        return apiClient.put(`/professionals/services/${id}`, data);
    },

    /**
     * Remove a service.
     */
    deleteService: (
        id: number,
    ): Promise<AxiosResponse<ProfessionalServiceDeleteResponse>> => {
        return apiClient.delete(`/professionals/services/${id}`);
    },
};

export default serviceApi;
