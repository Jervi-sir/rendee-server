import React from 'react';
import { TouchableOpacity, View } from 'react-native';
import { useTheme } from '../ThemeProvider';

export interface CardProps {
  children: React.ReactNode;
  onPress?: () => void;
  padded?: boolean;
}

export function Card({ children, onPress, padded = true }: CardProps) {
  const { colors, spacing, radii } = useTheme();

  const content = (
    <View
      style={{
        backgroundColor: colors.surface,
        borderRadius: radii.lg,
        padding: padded ? spacing.lg : 0,
        borderWidth: 1,
        borderColor: colors.borderLight,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.04,
        shadowRadius: 3,
        elevation: 1,
      }}
    >
      {children}
    </View>
  );

  if (onPress) {
    return (
      <TouchableOpacity onPress={onPress} activeOpacity={0.7}>
        {content}
      </TouchableOpacity>
    );
  }

  return content;
}
