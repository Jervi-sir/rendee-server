import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import { Avatar } from './Avatar';
import type { Patient } from '../types';

export interface PatientCardProps {
  patient: Patient;
  onPress?: () => void;
}

function computeAge(dateOfBirth: string | null): number | null {
  if (!dateOfBirth) return null;
  const dob = new Date(dateOfBirth);
  const now = new Date();
  let age = now.getFullYear() - dob.getFullYear();
  const monthDiff = now.getMonth() - dob.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < dob.getDate())) {
    age--;
  }
  return age;
}

export function PatientCard({ patient, onPress }: PatientCardProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const displayName = patient.user?.full_name ?? 'Patient';
  const age = computeAge(patient.date_of_birth);
  const genderLabel = patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : null;

  const metaParts: string[] = [];
  if (age !== null) metaParts.push(`${age} yrs`);
  if (genderLabel) metaParts.push(genderLabel);
  if (patient.city) metaParts.push(patient.city);

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
          imageUrl={patient.user?.image_url}
          name={displayName}
          size={48}
        />

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
            {displayName}
          </Text>

          {metaParts.length > 0 ? (
            <Text
              style={{
                fontSize: typography.bodySmall.fontSize,
                fontWeight: typography.bodySmall.fontWeight,
                lineHeight: typography.bodySmall.lineHeight,
                color: colors.textMuted,
                marginTop: 2,
              }}
            >
              {metaParts.join(' · ')}
            </Text>
          ) : null}
        </View>
      </View>
    </TouchableOpacity>
  );
}
