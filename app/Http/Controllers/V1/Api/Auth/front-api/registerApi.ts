import { AxiosResponse } from 'axios';
import apiClient from './apiClient';
import { RegisterInput, AuthResponse } from './types';

export const registerApi = {
  /**
   * Register a new user (Patient, Center, Pharmacist, or Professional).
   */
  register: (data: RegisterInput): Promise<AxiosResponse<AuthResponse>> => {
    return apiClient.post('/auth/register', data);
  },
};

export default registerApi;
