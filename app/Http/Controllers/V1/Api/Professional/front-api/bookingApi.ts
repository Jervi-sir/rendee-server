// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    ProfessionalBookingsResponse,
    ProfessionalBookingUpdateInput,
    ProfessionalBookingUpdateResponse,
    ProfessionalBookingSuggestInput,
    ProfessionalBookingSuggestResponse,
} from './types';

export const bookingApi = {
    /**
     * Get list of appointments for the professional, grouped by tabs.
     */
    getBookings: (params?: {
        tab?: string;
    }): Promise<AxiosResponse<ProfessionalBookingsResponse>> => {
        return apiClient.get('/professionals/bookings', { params });
    },

    /**
     * Update booking status (confirm or reject).
     */
    updateBooking: (
        id: number,
        data: ProfessionalBookingUpdateInput,
    ): Promise<AxiosResponse<ProfessionalBookingUpdateResponse>> => {
        return apiClient.patch(`/professionals/bookings/${id}`, data);
    },

    /**
     * Propose alternative date/time for an appointment (reschedule).
     */
    suggestBookingTime: (
        id: number,
        data: ProfessionalBookingSuggestInput,
    ): Promise<AxiosResponse<ProfessionalBookingSuggestResponse>> => {
        return apiClient.post(`/professionals/bookings/${id}/suggest`, data);
    },
};

export default bookingApi;
