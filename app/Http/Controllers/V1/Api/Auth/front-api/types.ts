export type UserRoleCode = 'patient' | 'professional' | 'center' | 'pharmacist' | 'pharmacy' | 'doctor' | 'psychologist' | 'dentist';
export type ProfessionCode = 'doctor' | 'psychologist' | 'dentist';
export type DeviceType = 'ios' | 'android' | 'web';

export interface UserRole {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface UserDevice {
  id: number;
  user_id: number;
  device_id: string;
  device_name: string | null;
  device_type: DeviceType | null;
  device_model: string | null;
  os_version: string | null;
  app_version: string | null;
  push_notification_token: string | null;
  push_notifications_enabled: boolean;
  language: string;
  timezone: string;
}

export interface PatientProfile {
  id: number;
  user_id: number;
  date_of_birth: string | null;
  gender: string | null;
  address: string | null;
  city: string | null;
  medical_notes: string | null;
}

export interface ProfessionalProfile {
  id: number;
  user_id: number;
  profession_code: ProfessionCode;
  speciality_code: string | null;
  license_number: string | null;
  years_experience: string | null;
  phone_public: string | null;
  bio: string | null;
  address: string | null;
  city: string | null;
  is_available: boolean;
}

export interface CenterProfile {
  id: number;
  user_id: number;
  name: string;
  center_catalog_code: string | null;
  license_number: string | null;
  phone_public: string | null;
  description: string | null;
  address: string | null;
  city: string | null;
  emergency_24_7: boolean;
  is_active: boolean;
}

export interface PharmacyProfile {
  id: number;
  user_id: number;
  name: string | null;
  wilaya_code: string | null;
  location: string | null;
  bio: string | null;
  latitude: number | null;
  longitude: number | null;
  is_available: boolean;
}

export interface User {
  id: number;
  user_role_code: string;
  name: string;
  full_name: string | null;
  email: string;
  phone_number: string | null;
  profile_complete: boolean;
  created_at: string;
  updated_at: string;
  user_role?: UserRole;
  user_device?: UserDevice | null;
  patient?: PatientProfile | null;
  professional?: ProfessionalProfile | null;
  center?: CenterProfile | null;
  pharmacy?: PharmacyProfile | null;
}

// Response Formats
export interface AuthResponse {
  message: string;
  token_type: string;
  access_token: string;
  user: User;
}

export interface MeResponse {
  user: User;
}

export interface LogoutResponse {
  message: string;
}

export interface DeviceResponse {
  message: string;
  device: UserDevice;
}

// Request Inputs
export interface RegisterInput {
  user_role_code: UserRoleCode;
  profession_code?: ProfessionCode;
  name?: string;
  full_name?: string;
  email: string;
  phone_number?: string;
  password: string;
  password_confirmation: string;
  device_name?: string;
}

export interface LoginInput {
  email: string;
  password: string;
  device_name?: string;
}

export interface DeviceInput {
  device_id: string;
  device_name?: string;
  device_type?: DeviceType;
  device_model?: string;
  os_version?: string;
  app_version?: string;
  push_notification_token?: string;
  push_notification_token_sandbox?: string;
  push_notifications_enabled?: boolean;
  language?: string;
  timezone?: string;
  notification_preferences?: Record<string, any>;
}
