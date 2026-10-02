import { api } from "@/utils/auth";

// ─────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────

export interface ProfessionalHeader {
  partner_name?: string;
  professional_name: string;
  speciality: string;
  date_label: string;
}

export interface ProfessionalStats {
  pending: number;
  in_progress: number;
  confirmed: number;
  completed: number;
  cancelled: number;
  today_appointments: number;
  completed_today: number;
  total_completed: number;
  pending_requests: number;
}

export interface ProfessionalStatListItem {
  key: string;
  label: string;
  value: string;
}

export interface ProfessionalAppointmentItem {
  id: number;
  reference: string;
  patient_name: string;
  patient_phone: string;
  visit_type: string;
  service_name: string;
  date: string;
  time: string;
  status_code: string;
  status: string;
  notes?: string | null;
  created_at?: string | null;
  active: boolean;
}

export interface GetProfessionalDashboardParams {
  /** Page number for appointments pagination (default: 1) */
  page?: number;
  /** Items per page (default: 10) */
  per_page?: number;
}

export interface GetProfessionalDashboardResponse {
  header: ProfessionalHeader;
  stats: ProfessionalStats;
  stats_list: ProfessionalStatListItem[];
  appointments: ProfessionalAppointmentItem[];
  current_page: number;
  next_page: number | null;
  total: number;
  agenda: ProfessionalAppointmentItem[];
}

// ─────────────────────────────────────────────
// API Calls
// ─────────────────────────────────────────────

/**
 * Fetch dashboard stats and paginated newest appointments for the authenticated professional.
 *
 * **Endpoint:** `GET /partner/dashboard`
 *
 * @example
 * ```ts
 * const { header, stats, appointments, current_page, next_page, total } = await getProfessionalDashboard({
 *   page: 1,
 *   per_page: 10,
 * });
 * ```
 */
export async function getProfessionalDashboard(
  params?: GetProfessionalDashboardParams,
): Promise<GetProfessionalDashboardResponse> {
  const response = await api.get<GetProfessionalDashboardResponse>(
    "/partner/dashboard",
    {
      params,
    },
  );
  return response.data;
}
