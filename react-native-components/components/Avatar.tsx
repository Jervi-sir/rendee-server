import React from 'react';
import { View, Image, Text } from 'react-native';
import { useTheme } from '../ThemeProvider';

export interface AvatarProps {
  imageUrl?: string | null;
  name: string;
  size?: number;
}

export function Avatar({ imageUrl, name, size = 48 }: AvatarProps) {
  const { colors, typography } = useTheme();

  const initials = name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('');

  if (imageUrl) {
    return (
      <Image
        source={{ uri: imageUrl }}
        style={{
          width: size,
          height: size,
          borderRadius: size / 2,
          backgroundColor: colors.borderLight,
        }}
      />
    );
  }

  return (
    <View
      style={{
        width: size,
        height: size,
        borderRadius: size / 2,
        backgroundColor: colors.accentLight,
        alignItems: 'center',
        justifyContent: 'center',
      }}
    >
      <Text
        style={{
          fontSize: size * 0.38,
          fontWeight: typography.label.fontWeight,
          color: colors.accent,
        }}
      >
        {initials}
      </Text>
    </View>
  );
}
