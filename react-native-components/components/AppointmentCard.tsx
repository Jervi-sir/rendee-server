import React from 'react';
import { View, Text } from 'react-native';
import { useTheme } from '../ThemeProvider';
import { StatusBadge } from './StatusBadge';
import type { Booking } from '../types';

export interface AppointmentCardProps {
  booking: Booking;
}

function formatDate(dateStr: string): string {
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}

function formatTime(timeStr: string): string {
  const [h, m] = timeStr.split(':');
  return `${h}:${m}`;
}

export function AppointmentCard({ booking }: AppointmentCardProps) {
  const { colors, spacing, typography, radii } = useTheme();

  return (
    <View
      style={{
        backgroundColor: colors.surface,
        borderRadius: radii.lg,
        padding: spacing.lg,
        borderWidth: 1,
        borderColor: colors.borderLight,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.04,
        shadowRadius: 3,
        elevation: 1,
      }}
    >
      {/* Header: reference + status */}
      <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: spacing.md }}>
        <Text
          style={{
            fontSize: typography.caption.fontSize,
            fontWeight: '500',
            lineHeight: typography.caption.lineHeight,
            color: colors.textMuted,
          }}
        >
          {booking.reference}
        </Text>

        {booking.status_code ? (
          <StatusBadge statusCode={booking.status_code} />
        ) : null}
      </View>

      {/* Date & Time */}
      <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm }}>
        <Text style={{ fontSize: 16, marginRight: spacing.sm }}>📅</Text>
        <Text
          style={{
            fontSize: typography.body.fontSize,
            fontWeight: '600',
            lineHeight: typography.body.lineHeight,
            color: colors.textPrimary,
          }}
        >
          {formatDate(booking.booking_date)}
        </Text>
        <Text
          style={{
            fontSize: typography.body.fontSize,
            fontWeight: typography.body.fontWeight,
            lineHeight: typography.body.lineHeight,
            color: colors.textSecondary,
            marginLeft: spacing.sm,
          }}
        >
          {formatTime(booking.booking_time)}
        </Text>
      </View>

      {/* Patient info */}
      <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm }}>
        <Text style={{ fontSize: 16, marginRight: spacing.sm }}>👤</Text>
        <Text
          style={{
            fontSize: typography.bodySmall.fontSize,
            fontWeight: typography.bodySmall.fontWeight,
            lineHeight: typography.bodySmall.lineHeight,
            color: colors.textSecondary,
          }}
        >
          {booking.patient_name}
        </Text>
        <Text
          style={{
            fontSize: typography.caption.fontSize,
            fontWeight: typography.caption.fontWeight,
            lineHeight: typography.caption.lineHeight,
            color: colors.textMuted,
            marginLeft: spacing.sm,
          }}
        >
          {booking.patient_phone}
        </Text>
      </View>

      {/* Proposed reschedule */}
      {booking.has_pending_proposal && booking.proposed_date ? (
        <View
          style={{
            backgroundColor: colors.warningLight,
            borderRadius: radii.md,
            padding: spacing.sm,
            marginBottom: spacing.sm,
          }}
        >
          <Text
            style={{
              fontSize: typography.caption.fontSize,
              fontWeight: '500',
              lineHeight: typography.caption.lineHeight,
              color: colors.warning,
            }}
          >
            ⏳ Proposed: {formatDate(booking.proposed_date)}{' '}
            {booking.proposed_time ? formatTime(booking.proposed_time) : ''}
          </Text>
        </View>
      ) : null}

      {/* Notes */}
      {booking.notes ? (
        <Text
          style={{
            fontSize: typography.bodySmall.fontSize,
            fontWeight: typography.bodySmall.fontWeight,
            lineHeight: typography.bodySmall.lineHeight,
            color: colors.textMuted,
            fontStyle: 'italic',
          }}
          numberOfLines={2}
        >
          {booking.notes}
        </Text>
      ) : null}
    </View>
  );
}
