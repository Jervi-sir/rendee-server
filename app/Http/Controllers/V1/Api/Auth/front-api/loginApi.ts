import { AxiosResponse } from 'axios';
import apiClient from './apiClient';
import { LoginInput, AuthResponse } from './types';

export const loginApi = {
  /**
   * Log in an existing user and get access token.
   */
  login: (data: LoginInput): Promise<AxiosResponse<AuthResponse>> => {
    return apiClient.post('/auth/login', data);
  },
};

export default loginApi;
