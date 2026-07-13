// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    BookingsListResponse,
    BookingDetailResponse,
    BookingInput,
    BookingStoreResponse,
} from './types';

export const bookingApi = {
    /**
     * Fetch list of appointments booked by the patient.
     */
    getBookings: (): Promise<AxiosResponse<BookingsListResponse>> => {
        return apiClient.get('/patients/bookings');
    },

    /**
     * Get appointment details.
     */
    getBookingDetail: (
        id: number,
    ): Promise<AxiosResponse<BookingDetailResponse>> => {
        return apiClient.get(`/patients/bookings/${id}`);
    },

    /**
     * Create a new booking request.
     */
    createBooking: (
        data: BookingInput,
    ): Promise<AxiosResponse<BookingStoreResponse>> => {
        return apiClient.post('/patients/bookings', data);
    },
};

export default bookingApi;
