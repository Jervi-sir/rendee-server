// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { PharmacistDashboardResponse } from './types';

export const dashboardApi = {
    /**
     * Get dashboard summary for the authenticated pharmacy/pharmacist.
     */
    getDashboard: (): Promise<AxiosResponse<PharmacistDashboardResponse>> => {
        return apiClient.get('/pharmacists/dashboard');
    },
};

export default dashboardApi;
