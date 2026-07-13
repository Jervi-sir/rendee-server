import { User } from '../../Auth/front-api/types';

export interface CenterProfileDetails {
  id: number;
  full_name: string;
  email: string;
  phone: string;
  name: string;
  type: string;
  license_number: string | null;
  phone_public: string | null;
  description: string | null;
  emergency_24_7: boolean;
  address: string | null;
  city: string | null;
  latitude: number | null;
  longitude: number | null;
  wilaya_code: string | null;
}

export interface CenterCatalogTypeOption {
  value: string;
  label: string;
}

export interface CenterProfileResponse {
  profile: CenterProfileDetails;
  types: CenterCatalogTypeOption[];
}

export interface CenterProfileUpdateInput {
  full_name: string;
  email: string;
  phone?: string;
  name: string;
  type: string;
  license_number?: string;
  phone_public?: string;
  description?: string;
  emergency_24_7: boolean;
  address?: string;
  city?: string;
  latitude?: number;
  longitude?: number;
  wilaya_code?: string;
}

export interface CenterProfileUpdateResponse {
  success: boolean;
  profile_complete: boolean;
  center: {
    id: number;
    user_id: number;
    name: string;
    center_catalog_code: string;
    license_number: string | null;
    phone_public: string | null;
    description: string | null;
    emergency_24_7: boolean;
    address: string | null;
    city: string | null;
    latitude: string | null;
    longitude: string | null;
    wilaya_code: string | null;
    user?: User;
  };
}

export interface CenterDashboardHeader {
  center_name: string;
  status_label: string;
}

export interface CenterDashboardStat {
  key: string;
  label: string;
  value: string;
}

export interface CenterDashboardServiceItem {
  id: number;
  name: string;
  price: string;
  type: string;
}

export interface CenterDashboardPendingAlert {
  count: number;
  title: string;
  subtitle: string;
}

export interface CenterDashboardResponse {
  header: CenterDashboardHeader;
  stats: CenterDashboardStat[];
  services: CenterDashboardServiceItem[];
  pending_bookings: CenterDashboardPendingAlert;
}

export interface CenterServiceItem {
  id: number;
  service_id: number;
  name: string;
  description: string | null;
  price: string;
  price_label: string;
  duration_minutes: number | null;
  duration_label: string;
  is_active: boolean;
  status_label: string;
}

export interface CenterServicesResponse {
  services: CenterServiceItem[];
}

export interface CenterServiceCatalogItem {
  id: number;
  name: string;
  code: string;
}

export interface CenterServiceCatalogResponse {
  services_catalog: CenterServiceCatalogItem[];
}

export interface CenterServiceDetailResponse {
  service: CenterServiceItem;
}

export interface CenterServiceStoreInput {
  service_id: number;
  description?: string;
  price?: number;
  duration_minutes?: number;
  is_active: boolean;
}

export interface CenterServiceStoreResponse {
  success: boolean;
  service: any;
}

export interface CenterServiceUpdateInput {
  service_id: number;
  description?: string;
  price?: number;
  duration_minutes?: number;
  is_active: boolean;
}

export interface CenterServiceUpdateResponse {
  success: boolean;
  service: any;
}

export interface CenterServiceDeleteResponse {
  success: boolean;
}

// Bookings
export interface CenterBookingTab {
  key: string;
  label: string;
  count: number;
}

export interface CenterBookingItem {
  id: number;
  reference: string;
  patient_name: string;
  service_name: string;
  date: string | null;
  time: string | null;
  status: string;
  status_key: string;
  proposed_date: string | null;
  proposed_time: string | null;
  has_pending_proposal: boolean;
  can_confirm: boolean;
  can_cancel: boolean;
  can_suggest_new_time: boolean;
}

export interface CenterBookingsResponse {
  tabs: CenterBookingTab[];
  bookings: CenterBookingItem[];
}

export interface CenterBookingUpdateInput {
  status: 'confirmed' | 'cancelled';
}

export interface CenterBookingUpdateResponse {
  success: boolean;
  booking: {
    id: number;
    status_code: string;
  };
}

export interface CenterBookingSuggestInput {
  proposed_date: string;
  proposed_time: string;
}

export interface CenterBookingSuggestResponse {
  success: boolean;
  booking: any;
}
