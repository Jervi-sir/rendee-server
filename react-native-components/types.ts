// ─── Catalog Types ───────────────────────────────────────────────────────────

export interface Profession {
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
  hex: string;
}

export interface Speciality {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface ServiceCatalog {
  id: number;
  code: string;
  source: string | null;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface Wilaya {
  id: number;
  code: string;
  number: string | null;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface ContactPlatform {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface CenterCatalog {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface Status {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

// ─── User Types ──────────────────────────────────────────────────────────────

export type UserRole = 'patient' | 'professional' | 'center';

export interface User {
  id: number;
  user_role_code: string | null;
  name: string | null;
  email: string;
  email_verified_at: string | null;
  full_name: string | null;
  image_url: string | null;
  phone_number: string | null;
  profile_complete: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface UserContact {
  id: number;
  user_id: number;
  platform_code: string | null;
  url: string | null;
  target_user_type: string | null;
}

// ─── Patient Types ───────────────────────────────────────────────────────────

export interface Patient {
  id: number;
  user_id: number;
  date_of_birth: string | null;
  gender: string | null;
  address: string | null;
  city: string | null;
  medical_notes: string | null;
  created_at: string | null;
  updated_at: string | null;
  user?: User;
}

// ─── Professional Types ──────────────────────────────────────────────────────

export interface Professional {
  id: number;
  user_id: number;
  profession_code: string;
  professional_speciality_code: string | null;
  wilaya_code: string | null;
  license_number: string | null;
  years_experience: string | null;
  phone_public: string | null;
  bio: string | null;
  address: string | null;
  city: string | null;
  latitude: number | null;
  longitude: number | null;
  is_available: boolean;
  created_at: string | null;
  updated_at: string | null;
  user?: User;
  speciality?: Speciality;
  profession?: Profession;
}

export interface ProfessionalSchedule {
  id: number;
  professional_id: number;
  day_of_week: number;
  start_time: string | null;
  end_time: string | null;
  is_active: boolean;
}

export interface ProfessionalService {
  id: number;
  professional_id: number;
  service_catalog_code: string | null;
  price: number | null;
  duration_minutes: number | null;
  service?: ServiceCatalog;
}

// ─── Center Types ────────────────────────────────────────────────────────────

export interface Center {
  id: number;
  user_id: number;
  name: string;
  center_catalog_code: string | null;
  wilaya_code: string | null;
  license_number: string | null;
  phone_public: string | null;
  description: string | null;
  address: string | null;
  city: string | null;
  latitude: number | null;
  longitude: number | null;
  emergency_24_7: boolean;
  is_active: boolean;
  created_at: string | null;
  updated_at: string | null;
  user?: User;
  catalog?: CenterCatalog;
}

export interface CenterWorkingHours {
  id: number;
  center_id: number;
  slot_date: string;
  start_time: string;
  end_time: string | null;
  is_available: boolean;
}

export interface CenterService {
  id: number;
  center_id: number;
  service_catalog_code: string | null;
  description: string | null;
  price: number | null;
  duration_minutes: number | null;
  is_active: boolean;
  service?: ServiceCatalog;
}

// ─── Booking Types ───────────────────────────────────────────────────────────

export interface Booking {
  id: number;
  reference: string;
  patient_id: number | null;
  bookable_type: string | null;
  bookable_id: number;
  service_type: string | null;
  service_id: number | null;
  schedule_type: string | null;
  schedule_id: number | null;
  patient_name: string;
  patient_phone: string;
  booking_date: string;
  booking_time: string;
  status_code: string | null;
  is_center: boolean;
  proposed_date: string | null;
  proposed_time: string | null;
  has_pending_proposal: boolean;
  notes: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface BookingHistory {
  id: number;
  booking_id: number;
  status_code: string | null;
  notes: string | null;
  changed_by: number | null;
  created_at: string | null;
  updated_at: string | null;
}

// ─── Notification Types ──────────────────────────────────────────────────────

export interface Notification {
  id: number;
  user_id: number;
  title: string;
  body: string;
  type: string | null;
  data: Record<string, unknown> | null;
  is_read: boolean;
  created_at: string | null;
  updated_at: string | null;
}

// ─── Pharmacist Types ────────────────────────────────────────────────────────

export interface Pharmacist {
  id: number;
  user_id: number;
  wilaya_code: string | null;
  name: string | null;
  phone_public: string | null;
  bio: string | null;
  address: string | null;
  city: string | null;
  latitude: number | null;
  longitude: number | null;
  is_available: boolean;
  created_at: string | null;
  updated_at: string | null;
  user?: User;
}
