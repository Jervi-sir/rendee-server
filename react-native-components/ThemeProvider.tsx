import React, { createContext, useContext, useMemo } from 'react';
import type { UserRole } from './types';
import { themesByRole, patientTheme } from './theme';
import type { ThemeTokens } from './theme';

const ThemeContext = createContext<ThemeTokens>(patientTheme);

export interface ThemeProviderProps {
  role: UserRole;
  children: React.ReactNode;
}

export function ThemeProvider({ role, children }: ThemeProviderProps) {
  const theme = useMemo(() => themesByRole[role], [role]);

  return (
    <ThemeContext.Provider value={theme}>
      {children}
    </ThemeContext.Provider>
  );
}

export function useTheme(): ThemeTokens {
  return useContext(ThemeContext);
}
