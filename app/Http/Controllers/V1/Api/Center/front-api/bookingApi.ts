// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
  CenterBookingsResponse,
  CenterBookingUpdateInput,
  CenterBookingUpdateResponse,
  CenterBookingSuggestInput,
  CenterBookingSuggestResponse,
} from './types';

export const bookingApi = {
  /**
   * Get list of appointments for the center, grouped by tabs.
   */
  getBookings: (params?: { tab?: string }): Promise<AxiosResponse<CenterBookingsResponse>> => {
    return apiClient.get('/centers/bookings', { params });
  },

  /**
   * Update booking status (confirm or cancel).
   */
  updateBooking: (
    id: number,
    data: CenterBookingUpdateInput
  ): Promise<AxiosResponse<CenterBookingUpdateResponse>> => {
    return apiClient.patch(`/centers/bookings/${id}`, data);
  },

  /**
   * Propose reschedule options (suggest new date/time).
   */
  suggestBookingTime: (
    id: number,
    data: CenterBookingSuggestInput
  ): Promise<AxiosResponse<CenterBookingSuggestResponse>> => {
    return apiClient.post(`/centers/bookings/${id}/suggest`, data);
  },
};

export default bookingApi;
