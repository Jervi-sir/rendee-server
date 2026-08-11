import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import { StatusBadge } from './StatusBadge';
import type { Booking } from '../types';

export interface AppointmentListItemProps {
  booking: Booking;
  onPress?: () => void;
}

function formatTime(timeStr: string): string {
  const [h, m] = timeStr.split(':');
  return `${h}:${m}`;
}

export function AppointmentListItem({ booking, onPress }: AppointmentListItemProps) {
  const { colors, spacing, typography, radii } = useTheme();

  return (
    <TouchableOpacity
      onPress={onPress}
      activeOpacity={0.7}
      style={{
        backgroundColor: colors.surface,
        borderRadius: radii.md,
        padding: spacing.md,
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: colors.borderLight,
      }}
    >
      {/* Time block */}
      <View
        style={{
          width: 56,
          alignItems: 'center',
          justifyContent: 'center',
          paddingVertical: spacing.xs,
          paddingHorizontal: spacing.sm,
          backgroundColor: colors.accentLight,
          borderRadius: radii.sm,
          marginRight: spacing.md,
        }}
      >
        <Text
          style={{
            fontSize: typography.label.fontSize,
            fontWeight: '600',
            lineHeight: typography.label.lineHeight,
            color: colors.accent,
          }}
        >
          {formatTime(booking.booking_time)}
        </Text>
      </View>

      {/* Center content */}
      <View style={{ flex: 1 }}>
        <Text
          style={{
            fontSize: typography.body.fontSize,
            fontWeight: '500',
            lineHeight: typography.body.lineHeight,
            color: colors.textPrimary,
          }}
          numberOfLines={1}
        >
          {booking.patient_name}
        </Text>

        <Text
          style={{
            fontSize: typography.caption.fontSize,
            fontWeight: typography.caption.fontWeight,
            lineHeight: typography.caption.lineHeight,
            color: colors.textMuted,
            marginTop: 2,
          }}
          numberOfLines={1}
        >
          {booking.reference}
          {booking.is_center ? ' · Center' : ''}
        </Text>
      </View>

      {/* Status */}
      {booking.status_code ? (
        <StatusBadge statusCode={booking.status_code} />
      ) : null}
    </TouchableOpacity>
  );
}
