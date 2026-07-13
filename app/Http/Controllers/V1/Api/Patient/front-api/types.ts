export interface UserSummary {
  id: number;
  name: string;
  full_name: string | null;
  email: string;
}

export interface SpecialtySummary {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

// Professional Types
export interface ProfessionalListItem {
  id: number;
  name: string;
  profession_code: string;
  speciality: string;
  years_experience: number;
  rating: string;
  reviews_count: number;
  patients_label: string;
  bio: string;
  address: string | null;
  city: string | null;
  phone: string | null;
  user: UserSummary | null;
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

export interface ProfessionalScheduleItem {
  id: number;
  professional_id: number;
  day_of_week: number;
  start_time: string;
  end_time: string;
  is_active: boolean;
}

export interface ProfessionalDetail extends ProfessionalListItem {
  working_hours: string;
  qualifications: Array<{ id: number; label: string }>;
  services: ProfessionalServiceItem[];
  schedules: ProfessionalScheduleItem[];
  contacts: any[];
}

export interface ProfessionalsListResponse {
  professionals: ProfessionalListItem[];
}

export interface ProfessionalDetailResponse {
  professional: ProfessionalDetail;
}

// Center Types
export interface CenterServiceItem {
  id: number;
  name: string;
  price: string | null;
  duration_minutes: number | null;
}

export interface CenterListItem {
  id: number;
  name: string;
  type: string;
  description: string | null;
  rating: string;
  reviews_count: number;
  emergency_label: string;
  distance: string;
  address: string | null;
  city: string | null;
  phone: string | null;
  working_hours: string;
}

export interface CenterDetail extends CenterListItem {
  services: CenterServiceItem[];
  contacts: any[];
  working_hours_slots: any[];
}

export interface CentersListResponse {
  centers: CenterListItem[];
}

export interface CenterDetailResponse {
  center: CenterDetail;
}

// Pharmacy Types
export interface PharmacyListItem {
  id: number;
  name: string;
  location: string | null;
  bio: string | null;
  latitude: number | null;
  longitude: number | null;
  is_available: boolean;
  wilaya_code: string | null;
  user: UserSummary | null;
}

export interface PharmaciesListResponse {
  pharmacies: PharmacyListItem[];
}

export interface PharmacyDetailResponse {
  pharmacy: PharmacyListItem;
}

// Map Types
export interface MapMarker {
  id: number;
  title: string;
  latitude: number;
  longitude: number;
  entity_type: 'professional' | 'center' | 'pharmacy';
  entity_id: number;
}

export interface MapSelectedCard {
  title: string;
  subtitle: string;
  entity_type: string;
  entity_id: number;
  latitude: number;
  longitude: number;
}

export interface MapDataResponse {
  markers: MapMarker[];
  selected_card: MapSelectedCard | null;
}

// Booking Types
export interface BookingInput {
  bookable_type: 'professional' | 'center';
  bookable_id: number;
  date: string; // Y-m-d
  time: string; // H:i
  service_id: number;
  patient_name: string;
  patient_phone: string;
  notes?: string;
}

export interface BookingResponseItem {
  id: number;
  reference: string;
  patient_id: number | null;
  bookable_type: string;
  bookable_id: number;
  booking_date: string;
  booking_time: string;
  patient_name: string;
  patient_phone: string;
  status_code: string;
  is_center: boolean;
  proposed_date: string | null;
  proposed_time: string | null;
  has_pending_proposal: boolean;
  notes: string | null;
  created_at: string;
  updated_at: string;
  bookable?: {
    id: number;
    user?: UserSummary;
  };
  service?: {
    id: number;
    price: string;
    service_catalog?: {
      ar: string | null;
      en: string | null;
    };
  };
  status?: {
    code: string;
    ar: string | null;
    en: string | null;
  };
}

export interface BookingsListResponse {
  bookings: BookingResponseItem[];
}

export interface BookingDetailResponse {
  booking: BookingResponseItem;
}

export interface BookingStoreResponse {
  success: boolean;
  message: string;
  booking: BookingResponseItem;
}

// Search Types
export interface PopularSpecialty {
  id: number;
  label: string;
  slug: string;
  professionals_count: number;
}

export interface RecentSearchItem {
  id: number;
  label: string;
  city: string | null;
  speciality: {
    id: number;
    label: string;
    slug: string;
  } | null;
  created_at: string;
}

export interface SearchResultItem {
  type: 'professional' | 'center' | 'pharmacy';
  id: number;
  name: string;
  subtitle: string;
  city: string | null;
  rating: number;
  reviews_count: number;
  image: string | null;
}

export interface SearchResponse {
  search_placeholder: string;
  popular_specialities: PopularSpecialty[];
  recent_searches: RecentSearchItem[];
  results: SearchResultItem[];
}

export interface PatientProfileResponse {
  user: {
    id: number;
    full_name: string;
    email: string;
    phone: string;
    profile_complete: boolean;
    patient: {
      date_of_birth: string | null;
      gender: 'male' | 'female' | null;
      address: string | null;
      city: string | null;
      medical_notes: string | null;
    } | null;
  };
}

export interface PatientProfileUpdateInput {
  full_name: string;
  phone?: string;
  date_of_birth: string;
  gender: 'male' | 'female';
  address: string;
  city: string;
  medical_notes: string;
}

