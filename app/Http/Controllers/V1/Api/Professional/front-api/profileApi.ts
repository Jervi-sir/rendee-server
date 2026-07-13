// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    ProfessionalProfileResponse,
    ProfessionalProfileUpdateInput,
    ProfessionalProfileUpdateResponse,
} from './types';

export const profileApi = {
    /**
     * Retrieve the authenticated professional's profile.
     */
    getProfile: (): Promise<AxiosResponse<ProfessionalProfileResponse>> => {
        return apiClient.get('/professionals/profile');
    },

    /**
     * Update the authenticated professional's profile.
     */
    updateProfile: (
        data: ProfessionalProfileUpdateInput,
    ): Promise<AxiosResponse<ProfessionalProfileUpdateResponse>> => {
        return apiClient.post('/professionals/profile', data);
    },
};

export default profileApi;
