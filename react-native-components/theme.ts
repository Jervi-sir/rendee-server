import type { UserRole } from './types';

// ─── Token Shape ─────────────────────────────────────────────────────────────

export interface ThemeColors {
  background: string;
  surface: string;
  surfacePressed: string;
  textPrimary: string;
  textSecondary: string;
  textMuted: string;
  accent: string;
  accentLight: string;
  accentPressed: string;
  border: string;
  borderLight: string;
  success: string;
  successLight: string;
  warning: string;
  warningLight: string;
  error: string;
  errorLight: string;
  info: string;
  infoLight: string;
  statusPending: string;
  statusPendingBg: string;
  statusConfirmed: string;
  statusConfirmedBg: string;
  statusCancelled: string;
  statusCancelledBg: string;
  statusCompleted: string;
  statusCompletedBg: string;
  availableOnline: string;
  availableOffline: string;
  overlay: string;
  tabBarBackground: string;
  tabBarBorder: string;
  tabBarInactive: string;
}

export interface ThemeSpacing {
  xs: number;
  sm: number;
  md: number;
  lg: number;
  xl: number;
  '2xl': number;
  '3xl': number;
}

export interface ThemeTypography {
  heading1: { fontSize: number; fontWeight: '700' | '800'; lineHeight: number };
  heading2: { fontSize: number; fontWeight: '600' | '700'; lineHeight: number };
  heading3: { fontSize: number; fontWeight: '600'; lineHeight: number };
  body: { fontSize: number; fontWeight: '400'; lineHeight: number };
  bodySmall: { fontSize: number; fontWeight: '400'; lineHeight: number };
  caption: { fontSize: number; fontWeight: '400'; lineHeight: number };
  label: { fontSize: number; fontWeight: '500' | '600'; lineHeight: number };
}

export interface ThemeRadii {
  sm: number;
  md: number;
  lg: number;
  xl: number;
  full: number;
}

export interface ThemeTokens {
  role: UserRole;
  colors: ThemeColors;
  spacing: ThemeSpacing;
  typography: ThemeTypography;
  radii: ThemeRadii;
}

// ─── Shared Tokens ───────────────────────────────────────────────────────────

const sharedSpacing: ThemeSpacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  '2xl': 32,
  '3xl': 40,
};

const sharedTypography: ThemeTypography = {
  heading1: { fontSize: 28, fontWeight: '700', lineHeight: 36 },
  heading2: { fontSize: 22, fontWeight: '600', lineHeight: 28 },
  heading3: { fontSize: 18, fontWeight: '600', lineHeight: 24 },
  body: { fontSize: 15, fontWeight: '400', lineHeight: 22 },
  bodySmall: { fontSize: 13, fontWeight: '400', lineHeight: 18 },
  caption: { fontSize: 11, fontWeight: '400', lineHeight: 14 },
  label: { fontSize: 13, fontWeight: '500', lineHeight: 18 },
};

const sharedRadii: ThemeRadii = {
  sm: 6,
  md: 10,
  lg: 14,
  xl: 20,
  full: 9999,
};

const sharedStatusColors = {
  statusPending: '#D97706',
  statusPendingBg: '#FEF3C7',
  statusConfirmed: '#059669',
  statusConfirmedBg: '#D1FAE5',
  statusCancelled: '#DC2626',
  statusCancelledBg: '#FEE2E2',
  statusCompleted: '#2563EB',
  statusCompletedBg: '#DBEAFE',
  availableOnline: '#10B981',
  availableOffline: '#9CA3AF',
};

const sharedNeutralColors = {
  background: '#FAFAFA',
  surface: '#FFFFFF',
  surfacePressed: '#F5F5F5',
  textPrimary: '#111827',
  textSecondary: '#4B5563',
  textMuted: '#9CA3AF',
  border: '#E5E7EB',
  borderLight: '#F3F4F6',
  success: '#059669',
  successLight: '#D1FAE5',
  warning: '#D97706',
  warningLight: '#FEF3C7',
  error: '#DC2626',
  errorLight: '#FEE2E2',
  info: '#2563EB',
  infoLight: '#DBEAFE',
  overlay: 'rgba(0,0,0,0.4)',
  tabBarBackground: '#FFFFFF',
  tabBarBorder: '#F3F4F6',
  tabBarInactive: '#9CA3AF',
};

// ─── Patient Theme (Teal) ────────────────────────────────────────────────────

export const patientTheme: ThemeTokens = {
  role: 'patient',
  colors: {
    ...sharedNeutralColors,
    ...sharedStatusColors,
    accent: '#0D9488',
    accentLight: '#CCFBF1',
    accentPressed: '#0F766E',
  },
  spacing: sharedSpacing,
  typography: sharedTypography,
  radii: sharedRadii,
};

// ─── Professional Theme (Indigo) ─────────────────────────────────────────────

export const professionalTheme: ThemeTokens = {
  role: 'professional',
  colors: {
    ...sharedNeutralColors,
    ...sharedStatusColors,
    accent: '#6366F1',
    accentLight: '#E0E7FF',
    accentPressed: '#4F46E5',
  },
  spacing: sharedSpacing,
  typography: sharedTypography,
  radii: sharedRadii,
};

// ─── Center Theme (Amber) ────────────────────────────────────────────────────

export const centerTheme: ThemeTokens = {
  role: 'center',
  colors: {
    ...sharedNeutralColors,
    ...sharedStatusColors,
    accent: '#F59E0B',
    accentLight: '#FEF3C7',
    accentPressed: '#D97706',
  },
  spacing: sharedSpacing,
  typography: sharedTypography,
  radii: sharedRadii,
};

// ─── Lookup ──────────────────────────────────────────────────────────────────

export const themesByRole: Record<UserRole, ThemeTokens> = {
  patient: patientTheme,
  professional: professionalTheme,
  center: centerTheme,
};
