// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';

export const bookingApi = {
    /**
     * Get pharmacy appointments (Stub placeholder).
     */
    getBookings: (): Promise<AxiosResponse<any>> => {
        return apiClient.get('/pharmacists/bookings');
    },
};

export default bookingApi;
