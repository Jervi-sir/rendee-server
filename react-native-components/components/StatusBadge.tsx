import React from 'react';
import { View, Text } from 'react-native';
import { useTheme } from '../ThemeProvider';

export interface StatusBadgeProps {
  statusCode: string;
}

const statusLabels: Record<string, string> = {
  pending: 'Pending',
  confirmed: 'Confirmed',
  cancelled: 'Cancelled',
  completed: 'Completed',
  no_show: 'No Show',
  rescheduled: 'Rescheduled',
};

export function StatusBadge({ statusCode }: StatusBadgeProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const colorMap: Record<string, { bg: string; fg: string }> = {
    pending: { bg: colors.statusPendingBg, fg: colors.statusPending },
    confirmed: { bg: colors.statusConfirmedBg, fg: colors.statusConfirmed },
    cancelled: { bg: colors.statusCancelledBg, fg: colors.statusCancelled },
    completed: { bg: colors.statusCompletedBg, fg: colors.statusCompleted },
    no_show: { bg: colors.errorLight, fg: colors.error },
    rescheduled: { bg: colors.warningLight, fg: colors.warning },
  };

  const palette = colorMap[statusCode] ?? { bg: colors.borderLight, fg: colors.textMuted };
  const label = statusLabels[statusCode] ?? statusCode;

  return (
    <View
      style={{
        backgroundColor: palette.bg,
        paddingVertical: spacing.xs,
        paddingHorizontal: spacing.sm,
        borderRadius: radii.full,
        alignSelf: 'flex-start',
      }}
    >
      <Text
        style={{
          fontSize: typography.caption.fontSize,
          fontWeight: '600',
          lineHeight: typography.caption.lineHeight,
          color: palette.fg,
          textTransform: 'capitalize',
        }}
      >
        {label}
      </Text>
    </View>
  );
}
