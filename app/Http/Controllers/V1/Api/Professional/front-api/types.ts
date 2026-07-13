import { User } from '../../Auth/front-api/types';

export interface ProfessionalProfileDetails {
  id: number;
  full_name: string;
  email: string;
  phone: string;
  specialty_id: number | null;
  speciality: string;
  license_number: string | null;
  years_experience: string | null;
  phone_public: string | null;
  bio: string | null;
  address: string | null;
  city: string | null;
  is_available: boolean;
  profession_code: string;
}

export interface ProfessionalSpecialityOption {
  id: number;
  label: string;
  code: string;
}

export interface ProfessionalProfileResponse {
  profile: ProfessionalProfileDetails;
  specialities: ProfessionalSpecialityOption[];
}

export interface ProfessionalProfileUpdateInput {
  full_name: string;
  email: string;
  phone?: string;
  specialty_id?: number;
  license_number?: string;
  years_experience?: string;
  phone_public?: string;
  bio?: string;
  address?: string;
  city?: string;
  is_available: boolean;
}

export interface ProfessionalProfileUpdateResponse {
  success: boolean;
  profile_complete: boolean;
  professional: {
    id: number;
    user_id: number;
    speciality_code: string | null;
    license_number: string | null;
    years_experience: string | null;
    phone_public: string | null;
    bio: string | null;
    address: string | null;
    city: string | null;
    is_available: boolean;
    user?: User;
  };
}

export interface ProfessionalDashboardHeader {
  professional_name: string;
  speciality: string;
  date_label: string;
}

export interface ProfessionalDashboardStat {
  key: string;
  label: string;
  value: string;
}

export interface ProfessionalDashboardAgendaItem {
  id: number;
  time: string;
  date: string;
  patient_name: string;
  visit_type: string;
  status: string;
  active: boolean;
}

export interface ProfessionalDashboardResponse {
  header: ProfessionalDashboardHeader;
  stats: ProfessionalDashboardStat[];
  agenda: ProfessionalDashboardAgendaItem[];
  actions: any[];
}

export interface ProfessionalServiceItem {
  id: number;
  professional_id: number;
  service_catalog_code: string;
  price: string;
  duration_minutes: number;
  service_catalog?: {
    id: number;
    code: string;
    en: string | null;
    fr: string | null;
    ar: string | null;
  };
}

export interface ProfessionalServicesResponse {
  services: ProfessionalServiceItem[];
}

export interface ProfessionalServiceCatalogItem {
  id: number;
  name: string;
  code: string;
}

export interface ProfessionalServiceCatalogResponse {
  catalog: ProfessionalServiceCatalogItem[];
}

export interface ProfessionalServiceStoreInput {
  service_catalog_code: string;
  price: number;
  duration_minutes: number;
}

export interface ProfessionalServiceStoreResponse {
  success: boolean;
  service: ProfessionalServiceItem;
}

export interface ProfessionalServiceUpdateInput {
  price: number;
  duration_minutes: number;
}

export interface ProfessionalServiceUpdateResponse {
  success: boolean;
  service: ProfessionalServiceItem;
}

export interface ProfessionalServiceDeleteResponse {
  success: boolean;
  message: string;
}

// Bookings
export interface ProfessionalBookingTab {
  key: string;
  label: string;
  count: number;
}

export interface ProfessionalBookingItem {
  id: number;
  reference: string;
  patient_name: string;
  date: string;
  time: string;
  visit_type: string;
  status: string;
  status_key: string;
  proposed_date: string | null;
  proposed_time: string | null;
  has_pending_proposal: boolean;
  can_confirm: boolean;
  can_reject: boolean;
  can_suggest_new_time: boolean;
}

export interface ProfessionalBookingsResponse {
  tabs: ProfessionalBookingTab[];
  appointments: ProfessionalBookingItem[];
}

export interface ProfessionalBookingUpdateInput {
  status: 'confirmed' | 'rejected';
}

export interface ProfessionalBookingUpdateResponse {
  success: boolean;
  booking: any;
}

export interface ProfessionalBookingSuggestInput {
  proposed_date: string;
  proposed_time: string;
}

export interface ProfessionalBookingSuggestResponse {
  success: boolean;
  booking: any;
}
