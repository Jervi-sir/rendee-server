import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import { Avatar } from './Avatar';
import type { Professional } from '../types';

export interface ProfessionalCardProps {
  professional: Professional;
  onPress?: () => void;
}

export function ProfessionalCard({ professional, onPress }: ProfessionalCardProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const displayName = professional.user?.full_name ?? 'Professional';
  const specialityLabel = professional.speciality?.fr ?? professional.speciality?.en ?? professional.professional_speciality_code ?? '';
  const professionLabel = professional.profession?.fr ?? professional.profession?.en ?? professional.profession_code;
  const locationText = [professional.city].filter(Boolean).join(', ');

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
      <View style={{ flexDirection: 'row', alignItems: 'center' }}>
        <Avatar
          imageUrl={professional.user?.image_url}
          name={displayName}
          size={52}
        />

        <View style={{ flex: 1, marginLeft: spacing.md }}>
          <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 2 }}>
            <Text
              style={{
                fontSize: typography.heading3.fontSize,
                fontWeight: typography.heading3.fontWeight,
                lineHeight: typography.heading3.lineHeight,
                color: colors.textPrimary,
                flex: 1,
              }}
              numberOfLines={1}
            >
              {displayName}
            </Text>

            <View
              style={{
                width: 8,
                height: 8,
                borderRadius: 4,
                backgroundColor: professional.is_available ? colors.availableOnline : colors.availableOffline,
                marginLeft: spacing.sm,
              }}
            />
          </View>

          <Text
            style={{
              fontSize: typography.bodySmall.fontSize,
              fontWeight: typography.bodySmall.fontWeight,
              lineHeight: typography.bodySmall.lineHeight,
              color: colors.accent,
              marginBottom: 2,
            }}
            numberOfLines={1}
          >
            {professionLabel}
            {specialityLabel ? ` · ${specialityLabel}` : ''}
          </Text>

          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            {locationText ? (
              <Text
                style={{
                  fontSize: typography.caption.fontSize,
                  fontWeight: typography.caption.fontWeight,
                  lineHeight: typography.caption.lineHeight,
                  color: colors.textMuted,
                }}
              >
                📍 {locationText}
              </Text>
            ) : null}

            {professional.years_experience ? (
              <Text
                style={{
                  fontSize: typography.caption.fontSize,
                  fontWeight: typography.caption.fontWeight,
                  lineHeight: typography.caption.lineHeight,
                  color: colors.textMuted,
                  marginLeft: locationText ? spacing.md : 0,
                }}
              >
                {professional.years_experience} yrs exp.
              </Text>
            ) : null}
          </View>
        </View>
      </View>
    </TouchableOpacity>
  );
}
