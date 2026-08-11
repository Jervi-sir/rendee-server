import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import type { Center } from '../types';

export interface CenterCardProps {
  center: Center;
  onPress?: () => void;
}

export function CenterCard({ center, onPress }: CenterCardProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const locationText = [center.address, center.city].filter(Boolean).join(', ');

  return (
    <TouchableOpacity
      onPress={onPress}
      activeOpacity={0.7}
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
      {/* Header row */}
      <View style={{ flexDirection: 'row', alignItems: 'flex-start', marginBottom: spacing.md }}>
        {/* Icon placeholder */}
        <View
          style={{
            width: 44,
            height: 44,
            borderRadius: radii.md,
            backgroundColor: colors.accentLight,
            alignItems: 'center',
            justifyContent: 'center',
          }}
        >
          <Text style={{ fontSize: 20 }}>🏥</Text>
        </View>

        <View style={{ flex: 1, marginLeft: spacing.md }}>
          <Text
            style={{
              fontSize: typography.heading3.fontSize,
              fontWeight: typography.heading3.fontWeight,
              lineHeight: typography.heading3.lineHeight,
              color: colors.textPrimary,
            }}
            numberOfLines={1}
          >
            {center.name}
          </Text>

          {center.center_catalog_code ? (
            <Text
              style={{
                fontSize: typography.caption.fontSize,
                fontWeight: typography.caption.fontWeight,
                lineHeight: typography.caption.lineHeight,
                color: colors.textMuted,
                marginTop: 2,
                textTransform: 'capitalize',
              }}
            >
              {center.center_catalog_code.replace(/_/g, ' ')}
            </Text>
          ) : null}
        </View>

        {/* Active indicator */}
        <View
          style={{
            width: 8,
            height: 8,
            borderRadius: 4,
            backgroundColor: center.is_active ? colors.availableOnline : colors.availableOffline,
            marginTop: spacing.sm,
          }}
        />
      </View>

      {/* Location */}
      {locationText ? (
        <Text
          style={{
            fontSize: typography.bodySmall.fontSize,
            fontWeight: typography.bodySmall.fontWeight,
            lineHeight: typography.bodySmall.lineHeight,
            color: colors.textSecondary,
            marginBottom: spacing.sm,
          }}
          numberOfLines={2}
        >
          📍 {locationText}
        </Text>
      ) : null}

      {/* Badges row */}
      <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.sm }}>
        {center.emergency_24_7 ? (
          <View
            style={{
              backgroundColor: colors.errorLight,
              paddingVertical: spacing.xs,
              paddingHorizontal: spacing.sm,
              borderRadius: radii.full,
            }}
          >
            <Text
              style={{
                fontSize: typography.caption.fontSize,
                fontWeight: '600',
                color: colors.error,
              }}
            >
              🚨 24/7
            </Text>
          </View>
        ) : null}

        {center.phone_public ? (
          <Text
            style={{
              fontSize: typography.caption.fontSize,
              fontWeight: typography.caption.fontWeight,
              lineHeight: typography.caption.lineHeight,
              color: colors.textMuted,
            }}
          >
            📞 {center.phone_public}
          </Text>
        ) : null}
      </View>
    </TouchableOpacity>
  );
}
