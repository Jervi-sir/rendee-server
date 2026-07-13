// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
  CenterProfileResponse,
  CenterProfileUpdateInput,
  CenterProfileUpdateResponse,
} from './types';

export const profileApi = {
  /**
   * Retrieve the center profile details and types catalog.
   */
  getProfile: (): Promise<AxiosResponse<CenterProfileResponse>> => {
    return apiClient.get('/centers/profile');
  },

  /**
   * Update the center profile.
   */
  updateProfile: (
    data: CenterProfileUpdateInput
  ): Promise<AxiosResponse<CenterProfileUpdateResponse>> => {
    return apiClient.post('/centers/profile', data);
  },
};

export default profileApi;
