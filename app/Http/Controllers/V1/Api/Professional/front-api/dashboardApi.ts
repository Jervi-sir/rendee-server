// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { ProfessionalDashboardResponse } from './types';

export const dashboardApi = {
    /**
     * Get dashboard summary.
     */
    getDashboard: (): Promise<AxiosResponse<ProfessionalDashboardResponse>> => {
        return apiClient.get('/professionals/dashboard');
    },
};

export default dashboardApi;
