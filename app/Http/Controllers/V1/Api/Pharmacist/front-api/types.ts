import { User } from '../../Auth/front-api/types';

export interface PharmacyProfileDetails {
  id: number;
  name: string;
  full_name: string;
  email: string;
  phone: string;
  location: string | null;
  bio: string | null;
  latitude: number | null;
  longitude: number | null;
  is_available: boolean;
  wilaya_code: string | null;
}

export interface PharmacyProfileResponse {
  profile: PharmacyProfileDetails;
}

export interface PharmacyProfileUpdateInput {
  name: string;
  full_name: string;
  email: string;
  phone?: string;
  location?: string;
  bio?: string;
  latitude?: number;
  longitude?: number;
  is_available: boolean;
  wilaya_code?: string;
}

export interface PharmacyProfileUpdateResponse {
  success: boolean;
  profile_complete: boolean;
  pharmacy: {
    id: number;
    user_id: number;
    name: string;
    location: string | null;
    bio: string | null;
    latitude: string | null;
    longitude: string | null;
    is_available: boolean;
    wilaya_code: string | null;
    user?: User;
  };
}

export interface PharmacistDashboardStat {
  key: string;
  label: string;
  value: string;
  status?: boolean;
}

export interface PharmacistDashboardResponse {
  header: {
    pharmacy_name: string;
    location: string;
    date_label: string;
  };
  stats: PharmacistDashboardStat[];
  info: {
    bio: string;
    city: string;
    wilaya_code: string;
  };
}

export interface PharmacistServiceItem {
  id: number;
  name: string;
  price: string;
  duration_minutes: number;
}

export interface PharmacistServicesResponse {
  services: PharmacistServiceItem[];
}
