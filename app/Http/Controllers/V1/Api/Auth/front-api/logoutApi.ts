import { AxiosResponse } from 'axios';
import apiClient from './apiClient';
import { LogoutResponse } from './types';

export const logoutApi = {
  /**
   * Log out the current user session and revoke the token.
   */
  logout: (): Promise<AxiosResponse<LogoutResponse>> => {
    return apiClient.post('/auth/logout');
  },
};

export default logoutApi;
