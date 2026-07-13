// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { CenterDashboardResponse } from './types';

export const dashboardApi = {
  /**
   * Get dashboard summary.
   */
  getDashboard: (): Promise<AxiosResponse<CenterDashboardResponse>> => {
    return apiClient.get('/centers/dashboard');
  },
};

export default dashboardApi;
