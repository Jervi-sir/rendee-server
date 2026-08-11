import React from 'react';
import { View, Text } from 'react-native';
import { useTheme } from '../ThemeProvider';
import { Avatar } from './Avatar';
import { Button } from './Button';
import type { Professional } from '../types';

export interface ProfessionalHeaderProps {
  professional: Professional;
  onContact?: () => void;
  onBookAppointment?: () => void;
}

export function ProfessionalHeader({
  professional,
  onContact,
  onBookAppointment,
}: ProfessionalHeaderProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const displayName = professional.user?.full_name ?? 'Professional';
  const specialityLabel = professional.speciality?.fr ?? professional.speciality?.en ?? '';
  const professionLabel = professional.profession?.fr ?? professional.profession?.en ?? professional.profession_code;

  return (
    <View
      style={{
        backgroundColor: colors.surface,
        paddingHorizontal: spacing.xl,
        paddingTop: spacing['3xl'],
        paddingBottom: spacing.xl,
        borderBottomLeftRadius: radii.xl,
        borderBottomRightRadius: radii.xl,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.06,
        shadowRadius: 8,
        elevation: 2,
      }}
    >
      {/* Avatar + Name */}
      <View style={{ alignItems: 'center', marginBottom: spacing.lg }}>
        <Avatar
          imageUrl={professional.user?.image_url}
          name={displayName}
          size={88}
        />

        <Text
          style={{
            fontSize: typography.heading1.fontSize,
            fontWeight: typography.heading1.fontWeight,
            lineHeight: typography.heading1.lineHeight,
            color: colors.textPrimary,
            marginTop: spacing.md,
            textAlign: 'center',
          }}
        >
          {displayName}
        </Text>

        <Text
          style={{
            fontSize: typography.body.fontSize,
            fontWeight: typography.body.fontWeight,
            lineHeight: typography.body.lineHeight,
            color: colors.accent,
            marginTop: spacing.xs,
          }}
        >
          {professionLabel}
          {specialityLabel ? ` · ${specialityLabel}` : ''}
        </Text>

        {/* Availability */}
        <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: spacing.sm }}>
          <View
            style={{
              width: 8,
              height: 8,
              borderRadius: 4,
              backgroundColor: professional.is_available ? colors.availableOnline : colors.availableOffline,
              marginRight: spacing.xs,
            }}
          />
          <Text
            style={{
              fontSize: typography.caption.fontSize,
              fontWeight: typography.caption.fontWeight,
              lineHeight: typography.caption.lineHeight,
              color: professional.is_available ? colors.availableOnline : colors.textMuted,
            }}
          >
            {professional.is_available ? 'Available' : 'Unavailable'}
          </Text>
        </View>
      </View>

      {/* Bio */}
      {professional.bio ? (
        <Text
          style={{
            fontSize: typography.bodySmall.fontSize,
            fontWeight: typography.bodySmall.fontWeight,
            lineHeight: typography.bodySmall.lineHeight,
            color: colors.textSecondary,
            textAlign: 'center',
            marginBottom: spacing.lg,
          }}
          numberOfLines={3}
        >
          {professional.bio}
        </Text>
      ) : null}

      {/* Stats row */}
      <View
        style={{
          flexDirection: 'row',
          justifyContent: 'center',
          marginBottom: spacing.xl,
          gap: spacing['2xl'],
        }}
      >
        {professional.years_experience ? (
          <View style={{ alignItems: 'center' }}>
            <Text
              style={{
                fontSize: typography.heading2.fontSize,
                fontWeight: typography.heading2.fontWeight,
                lineHeight: typography.heading2.lineHeight,
                color: colors.textPrimary,
              }}
            >
              {professional.years_experience}
            </Text>
            <Text
              style={{
                fontSize: typography.caption.fontSize,
                fontWeight: typography.caption.fontWeight,
                lineHeight: typography.caption.lineHeight,
                color: colors.textMuted,
              }}
            >
              Years Exp.
            </Text>
          </View>
        ) : null}

        {professional.city ? (
          <View style={{ alignItems: 'center' }}>
            <Text
              style={{
                fontSize: typography.heading2.fontSize,
                fontWeight: typography.heading2.fontWeight,
                lineHeight: typography.heading2.lineHeight,
                color: colors.textPrimary,
              }}
            >
              📍
            </Text>
            <Text
              style={{
                fontSize: typography.caption.fontSize,
                fontWeight: typography.caption.fontWeight,
                lineHeight: typography.caption.lineHeight,
                color: colors.textMuted,
              }}
            >
              {professional.city}
            </Text>
          </View>
        ) : null}
      </View>

      {/* Action buttons */}
      <View style={{ flexDirection: 'row', gap: spacing.sm }}>
        <View style={{ flex: 1 }}>
          <Button title="Book Appointment" onPress={onBookAppointment} variant="filled" />
        </View>
        <View style={{ flex: 1 }}>
          <Button title="Contact" onPress={onContact} variant="outline" />
        </View>
      </View>
    </View>
  );
}
