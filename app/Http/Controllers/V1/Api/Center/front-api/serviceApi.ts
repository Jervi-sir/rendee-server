// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
  CenterServicesResponse,
  CenterServiceCatalogResponse,
  CenterServiceDetailResponse,
  CenterServiceStoreInput,
  CenterServiceStoreResponse,
  CenterServiceUpdateInput,
  CenterServiceUpdateResponse,
  CenterServiceDeleteResponse,
} from './types';

export const serviceApi = {
  /**
   * List all center services.
   */
  getServices: (): Promise<AxiosResponse<CenterServicesResponse>> => {
    return apiClient.get('/centers/services');
  },

  /**
   * Get global service catalogs for centers.
   */
  getCatalog: (): Promise<AxiosResponse<CenterServiceCatalogResponse>> => {
    return apiClient.get('/centers/services/catalog');
  },

  /**
   * Add a service to the center profile.
   */
  addService: (
    data: CenterServiceStoreInput
  ): Promise<AxiosResponse<CenterServiceStoreResponse>> => {
    return apiClient.post('/centers/services', data);
  },

  /**
   * Show details of a specific center service.
   */
  getServiceDetail: (id: number): Promise<AxiosResponse<CenterServiceDetailResponse>> => {
    return apiClient.get(`/centers/services/${id}`);
  },

  /**
   * Update center service configurations.
   */
  updateService: (
    id: number,
    data: CenterServiceUpdateInput
  ): Promise<AxiosResponse<CenterServiceUpdateResponse>> => {
    return apiClient.put(`/centers/services/${id}`, data);
  },

  /**
   * Delete a center service.
   */
  deleteService: (id: number): Promise<AxiosResponse<CenterServiceDeleteResponse>> => {
    return apiClient.delete(`/centers/services/${id}`);
  },
};

export default serviceApi;
