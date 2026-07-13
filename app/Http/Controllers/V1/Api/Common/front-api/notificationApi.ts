// @ts-nocheck
import { AxiosResponse } from 'axios';
import apiClient from '../../Auth/front-api/apiClient';
import {
    NotificationsResponse,
    NotificationReadResponse,
    NotificationReadAllResponse,
} from './types';

export const notificationApi = {
    /**
     * Get list of notifications for the authenticated user.
     */
    getNotifications: (): Promise<AxiosResponse<NotificationsResponse>> => {
        return apiClient.get('/common/notifications');
    },

    /**
     * Mark a specific notification as read.
     */
    readNotification: (
        id: number,
    ): Promise<AxiosResponse<NotificationReadResponse>> => {
        return apiClient.post(`/common/notifications/${id}/read`);
    },

    /**
     * Mark all user notifications as read.
     */
    readAllNotifications: (): Promise<
        AxiosResponse<NotificationReadAllResponse>
    > => {
        return apiClient.post('/common/notifications/read-all');
    },
};

export default notificationApi;
