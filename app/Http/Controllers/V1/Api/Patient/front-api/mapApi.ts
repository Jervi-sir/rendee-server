// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import { MapDataResponse } from './types';

export const mapApi = {
    /**
     * Get geo-coordinates of nearby doctors, clinics, and pharmacies.
     */
    getMapData: (): Promise<AxiosResponse<MapDataResponse>> => {
        return apiClient.get('/patients/map');
    },
};

export default mapApi;
