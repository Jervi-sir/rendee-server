// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { PatientProfileResponse, PatientProfileUpdateInput } from './types';

export const profileApi = {
    /**
     * Retrieve the patient profile details.
     */
    getProfile: (): Promise<AxiosResponse<PatientProfileResponse>> => {
        return apiClient.get('/patient/onboarding');
    },

    /**
     * Update the patient profile details.
     */
    updateProfile: (
        data: PatientProfileUpdateInput,
    ): Promise<AxiosResponse<PatientProfileResponse>> => {
        return apiClient.post('/patient/onboarding', data);
    },
};

export default profileApi;
