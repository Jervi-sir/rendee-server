// @ts-nocheck
import { api } from '@/utils/auth';
import type { ContactPlatform } from './catalogs.api';

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface UserContactItem {
    id: number;
    user_id: number;
    platform_code: string;
    platform?: ContactPlatform | null;
    url: string;
    target_user_type?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface UpsertContactPayload {
    id?: number;
    platform_code: string;
    url: string;
    target_user_type?: string;
}

export interface BatchUpsertContactsPayload {
    contacts: UpsertContactPayload[];
}

export interface ContactsListResponse {
    success: boolean;
    count: number;
    contacts: UserContactItem[];
}

export interface SingleContactResponse {
    success: boolean;
    message?: string;
    contact: UserContactItem;
}

export interface BatchContactsResponse {
    success: boolean;
    message?: string;
    count?: number;
    contacts: UserContactItem[];
}

export interface ActionResponse {
    success: boolean;
    message?: string;
    error?: string;
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Get all contacts for the authenticated user.
 *
 * **Endpoint:** `GET /contacts`
 */
export async function getContacts(): Promise<UserContactItem[]> {
    const response = await api.get<ContactsListResponse>('/contacts');
    return response.data.contacts || [];
}

/**
 * Get a single contact by ID.
 *
 * **Endpoint:** `GET /contacts/{id}`
 */
export async function getContact(id: number): Promise<UserContactItem> {
    const response = await api.get<SingleContactResponse>(`/contacts/${id}`);
    return response.data.contact;
}

/**
 * Save a single contact.
 *
 * **Endpoint:** `POST /contacts/upsert`
 */
export async function upsertContact(
    payload: UpsertContactPayload,
): Promise<UserContactItem> {
    const response = await api.post<SingleContactResponse>('/contacts/upsert', payload);
    return response.data.contact;
}

/**
 * Batch save multiple contacts at once.
 *
 * **Endpoint:** `POST /contacts/upsert`
 */
export async function batchUpsertContacts(
    contacts: UpsertContactPayload[],
): Promise<UserContactItem[]> {
    const response = await api.post<BatchContactsResponse>('/contacts/upsert', {
        contacts,
    });
    return response.data.contacts || [];
}

/**
 * Delete a contact by ID.
 *
 * **Endpoint:** `DELETE /contacts/{id}`
 */
export async function deleteContact(id: number): Promise<ActionResponse> {
    const response = await api.delete<ActionResponse>(`/contacts/${id}`);
    return response.data;
}
