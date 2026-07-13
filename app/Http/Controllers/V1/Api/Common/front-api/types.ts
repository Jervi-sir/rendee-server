export interface Wilaya {
  code: string;
  ar: string;
  en: string;
}

export interface ContactPlatform {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
}

export interface ServiceCatalogItem {
  id: number;
  code: string;
  en: string | null;
  fr: string | null;
  ar: string | null;
  source: string | null;
}

export interface NotificationItem {
  id: number;
  user_id: number;
  title: string;
  body: string;
  type: string;
  data: Record<string, any> | null;
  is_read: boolean;
  created_at: string;
  updated_at: string;
}

export interface WilayasResponse {
  wilayas: Wilaya[];
}

export interface ContactPlatformsResponse {
  platforms: ContactPlatform[];
}

export interface ServicesResponse {
  services: ServiceCatalogItem[];
}

export interface NotificationsResponse {
  notifications: NotificationItem[];
}

export interface NotificationReadResponse {
  success: boolean;
  notification: NotificationItem;
}

export interface NotificationReadAllResponse {
  success: boolean;
  message: string;
}
