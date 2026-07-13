// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    PharmacyProfileResponse,
    PharmacyProfileUpdateInput,
    PharmacyProfileUpdateResponse,
} from './types';

export const profileApi = {
    /**
     * Get the authenticated pharmacy's profile.
     */
    getProfile: (): Promise<AxiosResponse<PharmacyProfileResponse>> => {
        return apiClient.get('/pharmacists/profile');
    },

    /**
     * Update the pharmacy's profile details.
     */
    updateProfile: (
        data: PharmacyProfileUpdateInput,
    ): Promise<AxiosResponse<PharmacyProfileUpdateResponse>> => {
        return apiClient.post('/pharmacists/profile', data);
    },
};

export default profileApi;
