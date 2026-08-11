import React from 'react';
import {
  TouchableOpacity,
  Text,
  ActivityIndicator,
  type ViewStyle,
  type TextStyle,
} from 'react-native';
import { useTheme } from '../ThemeProvider';

export interface ButtonProps {
  title: string;
  onPress?: () => void;
  variant?: 'filled' | 'outline' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  loading?: boolean;
}

export function Button({
  title,
  onPress,
  variant = 'filled',
  size = 'md',
  disabled = false,
  loading = false,
}: ButtonProps) {
  const { colors, spacing, typography, radii } = useTheme();

  const sizeMap: Record<string, { paddingV: number; paddingH: number; text: TextStyle }> = {
    sm: { paddingV: spacing.sm, paddingH: spacing.md, text: { fontSize: typography.bodySmall.fontSize, fontWeight: typography.label.fontWeight, lineHeight: typography.bodySmall.lineHeight } },
    md: { paddingV: spacing.md, paddingH: spacing.xl, text: { fontSize: typography.body.fontSize, fontWeight: typography.label.fontWeight, lineHeight: typography.body.lineHeight } },
    lg: { paddingV: spacing.lg, paddingH: spacing['2xl'], text: { fontSize: typography.heading3.fontSize, fontWeight: '600' as const, lineHeight: typography.heading3.lineHeight } },
  };

  const s = sizeMap[size];
  const isDisabled = disabled || loading;

  const containerBase: ViewStyle = {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radii.md,
    paddingVertical: s.paddingV,
    paddingHorizontal: s.paddingH,
    opacity: isDisabled ? 0.5 : 1,
  };

  const containerVariant: ViewStyle =
    variant === 'filled'
      ? { backgroundColor: colors.accent }
      : variant === 'outline'
        ? { backgroundColor: 'transparent', borderWidth: 1.5, borderColor: colors.accent }
        : { backgroundColor: 'transparent' };

  const textColor: string =
    variant === 'filled' ? '#FFFFFF' : colors.accent;

  return (
    <TouchableOpacity
      onPress={onPress}
      disabled={isDisabled}
      activeOpacity={0.7}
      style={{ ...containerBase, ...containerVariant }}
    >
      {loading ? (
        <ActivityIndicator
          size="small"
          color={textColor}
          style={{ marginRight: spacing.sm }}
        />
      ) : null}
      <Text
        style={{
          ...s.text,
          color: textColor,
        }}
      >
        {title}
      </Text>
    </TouchableOpacity>
  );
}
