import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import type { UserRole } from '../types';

export interface BottomTabBarProps {
  role: UserRole;
  activeTab: string;
  onTabPress: (tab: string) => void;
}

interface TabItem {
  key: string;
  label: string;
  icon: string;
}

const tabsByRole: Record<UserRole, TabItem[]> = {
  patient: [
    { key: 'home', label: 'Home', icon: '🏠' },
    { key: 'search', label: 'Search', icon: '🔍' },
    { key: 'appointments', label: 'Bookings', icon: '📅' },
    { key: 'profile', label: 'Profile', icon: '👤' },
  ],
  professional: [
    { key: 'dashboard', label: 'Dashboard', icon: '📊' },
    { key: 'schedule', label: 'Schedule', icon: '🕐' },
    { key: 'appointments', label: 'Bookings', icon: '📅' },
    { key: 'profile', label: 'Profile', icon: '👤' },
  ],
  center: [
    { key: 'dashboard', label: 'Dashboard', icon: '📊' },
    { key: 'staff', label: 'Staff', icon: '👥' },
    { key: 'appointments', label: 'Bookings', icon: '📅' },
    { key: 'settings', label: 'Settings', icon: '⚙️' },
  ],
};

export function BottomTabBar({ role, activeTab, onTabPress }: BottomTabBarProps) {
  const { colors, spacing, typography } = useTheme();

  const tabs = tabsByRole[role];

  return (
    <View
      style={{
        flexDirection: 'row',
        backgroundColor: colors.tabBarBackground,
        borderTopWidth: 1,
        borderTopColor: colors.tabBarBorder,
        paddingBottom: spacing.xl,
        paddingTop: spacing.sm,
        paddingHorizontal: spacing.sm,
      }}
    >
      {tabs.map((tab) => {
        const isActive = activeTab === tab.key;

        return (
          <TouchableOpacity
            key={tab.key}
            onPress={() => onTabPress(tab.key)}
            activeOpacity={0.7}
            style={{
              flex: 1,
              alignItems: 'center',
              justifyContent: 'center',
              paddingVertical: spacing.xs,
            }}
          >
            <Text
              style={{
                fontSize: 22,
                marginBottom: spacing.xs,
                opacity: isActive ? 1 : 0.5,
              }}
            >
              {tab.icon}
            </Text>

            <Text
              style={{
                fontSize: typography.caption.fontSize,
                fontWeight: isActive ? '600' : typography.caption.fontWeight,
                lineHeight: typography.caption.lineHeight,
                color: isActive ? colors.accent : colors.tabBarInactive,
              }}
            >
              {tab.label}
            </Text>

            {/* Active indicator */}
            {isActive ? (
              <View
                style={{
                  width: 4,
                  height: 4,
                  borderRadius: 2,
                  backgroundColor: colors.accent,
                  marginTop: spacing.xs,
                }}
              />
            ) : null}
          </TouchableOpacity>
        );
      })}
    </View>
  );
}
