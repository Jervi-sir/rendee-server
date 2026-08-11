import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import type { ProfessionalService, CenterService } from '../types';

export interface ServiceListItemProps {
  service: ProfessionalService | CenterService;
  onPress?: () => void;
}

function isCenterService(s: ProfessionalService | CenterService): s is CenterService {
  return 'description' in s && 'is_active' in s;
}

export function ServiceListItem({ service, onPress }: ServiceListItemProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const code = service.service_catalog_code ?? 'Service';
  const label = code.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
  const description = isCenterService(service) ? service.description : null;

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
      {/* Service icon */}
      <View
        style={{
          width: 40,
          height: 40,
          borderRadius: radii.sm,
          backgroundColor: colors.accentLight,
          alignItems: 'center',
          justifyContent: 'center',
          marginRight: spacing.md,
        }}
      >
        <Text style={{ fontSize: 18 }}>🩺</Text>
      </View>

      {/* Info */}
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
          {label}
        </Text>

        {description ? (
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
            {description}
          </Text>
        ) : null}
      </View>

      {/* Price & Duration */}
      <View style={{ alignItems: 'flex-end' }}>
        {service.price !== null && service.price !== undefined ? (
          <Text
            style={{
              fontSize: typography.label.fontSize,
              fontWeight: '600',
              lineHeight: typography.label.lineHeight,
              color: colors.accent,
            }}
          >
            {service.price.toLocaleString()} DA
          </Text>
        ) : null}

        {service.duration_minutes !== null && service.duration_minutes !== undefined ? (
          <Text
            style={{
              fontSize: typography.caption.fontSize,
              fontWeight: typography.caption.fontWeight,
              lineHeight: typography.caption.lineHeight,
              color: colors.textMuted,
              marginTop: 2,
            }}
          >
            {service.duration_minutes} min
          </Text>
        ) : null}
      </View>
    </TouchableOpacity>
  );
}
