// ─── Theme ───────────────────────────────────────────────────────────────────
export { patientTheme, professionalTheme, centerTheme, themesByRole } from './theme';
export type { ThemeTokens, ThemeColors, ThemeSpacing, ThemeTypography, ThemeRadii } from './theme';

// ─── Theme Provider ──────────────────────────────────────────────────────────
export { ThemeProvider, useTheme } from './ThemeProvider';
export type { ThemeProviderProps } from './ThemeProvider';

// ─── Types ───────────────────────────────────────────────────────────────────
export type {
  UserRole,
  User,
  UserContact,
  Patient,
  Professional,
  ProfessionalSchedule,
  ProfessionalService,
  Center,
  CenterWorkingHours,
  CenterService,
  Booking,
  BookingHistory,
  Notification,
  Pharmacist,
  Profession,
  Speciality,
  ServiceCatalog,
  Wilaya,
  ContactPlatform,
  CenterCatalog,
  Status,
} from './types';

// ─── Mock Data ───────────────────────────────────────────────────────────────
export {
  mockUsers,
  mockPatients,
  mockProfessionals,
  mockSchedules,
  mockProfessionalServices,
  mockCenters,
  mockCenterWorkingHours,
  mockCenterServices,
  mockBookings,
  mockNotifications,
  mockProfessions,
  mockSpecialities,
} from './mockData';

// ─── Components ──────────────────────────────────────────────────────────────
export { Button } from './components/Button';
export type { ButtonProps } from './components/Button';

export { Card } from './components/Card';
export type { CardProps } from './components/Card';

export { Avatar } from './components/Avatar';
export type { AvatarProps } from './components/Avatar';

export { StatusBadge } from './components/StatusBadge';
export type { StatusBadgeProps } from './components/StatusBadge';

export { ProfessionalCard } from './components/ProfessionalCard';
export type { ProfessionalCardProps } from './components/ProfessionalCard';

export { CenterCard } from './components/CenterCard';
export type { CenterCardProps } from './components/CenterCard';

export { PatientCard } from './components/PatientCard';
export type { PatientCardProps } from './components/PatientCard';

export { AppointmentCard } from './components/AppointmentCard';
export type { AppointmentCardProps } from './components/AppointmentCard';

export { AppointmentListItem } from './components/AppointmentListItem';
export type { AppointmentListItemProps } from './components/AppointmentListItem';

export { AvailabilitySlotPicker } from './components/AvailabilitySlotPicker';
export type { AvailabilitySlotPickerProps } from './components/AvailabilitySlotPicker';

export { ProfessionalHeader } from './components/ProfessionalHeader';
export type { ProfessionalHeaderProps } from './components/ProfessionalHeader';

export { NotificationItem } from './components/NotificationItem';
export type { NotificationItemProps } from './components/NotificationItem';

export { ServiceListItem } from './components/ServiceListItem';
export type { ServiceListItemProps } from './components/ServiceListItem';

export { BottomTabBar } from './components/BottomTabBar';
export type { BottomTabBarProps } from './components/BottomTabBar';
