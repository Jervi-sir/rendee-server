import { AxiosResponse } from 'axios';
import apiClient from './apiClient';
import { DeviceInput, DeviceResponse } from './types';

export const deviceApi = {
  /**
   * Register/update device info and push token for push notifications.
   */
  registerDevice: (data: DeviceInput): Promise<AxiosResponse<DeviceResponse>> => {
    return apiClient.post('/auth/devices', data);
  },
};

export default deviceApi;
