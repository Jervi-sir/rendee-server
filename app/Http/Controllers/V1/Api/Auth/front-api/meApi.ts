import { AxiosResponse } from 'axios';
import apiClient from './apiClient';
import { MeResponse } from './types';

export const meApi = {
  /**
   * Retrieve the current authenticated user's profile and device info.
   */
  getMe: (): Promise<AxiosResponse<MeResponse>> => {
    return apiClient.get('/auth/me');
  },
};

export default meApi;
