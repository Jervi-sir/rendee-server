import React from 'react';
import { View, Text, TouchableOpacity } from 'react-native';
import { useTheme } from '../ThemeProvider';
import type { Notification } from '../types';

export interface NotificationItemProps {
  notification: Notification;
  onPress?: () => void;
}

function timeAgo(dateStr: string): string {
  const now = new Date();
  const date = new Date(dateStr);
  const diffMs = now.getTime() - date.getTime();
  const diffMin = Math.floor(diffMs / 60000);

  if (diffMin < 1) return 'Just now';
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffHrs = Math.floor(diffMin / 60);
  if (diffHrs < 24) return `${diffHrs}h ago`;
  const diffDays = Math.floor(diffHrs / 24);
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
}

export function NotificationItem({ notification, onPress }: NotificationItemProps) {
  const { colors, spacing, typography, radii } = useTheme();

  return (
    <TouchableOpacity
      onPress={onPress}
      activeOpacity={0.7}
      style={{
        backgroundColor: notification.is_read ? colors.surface : colors.accentLight,
        borderRadius: radii.md,
        padding: spacing.lg,
        flexDirection: 'row',
        alignItems: 'flex-start',
        borderWidth: 1,
        borderColor: notification.is_read ? colors.borderLight : colors.accent + '20',
      }}
    >
      {/* Unread dot */}
      {!notification.is_read ? (
        <View
          style={{
            width: 8,
            height: 8,
            borderRadius: 4,
            backgroundColor: colors.accent,
            marginTop: spacing.xs + 2,
            marginRight: spacing.md,
          }}
        />
      ) : (
        <View style={{ width: 8, marginRight: spacing.md }} />
      )}

      {/* Content */}
      <View style={{ flex: 1 }}>
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: spacing.xs }}>
          <Text
            style={{
              fontSize: typography.body.fontSize,
              fontWeight: notification.is_read ? '400' : '600',
              lineHeight: typography.body.lineHeight,
              color: colors.textPrimary,
              flex: 1,
            }}
            numberOfLines={1}
          >
            {notification.title}
          </Text>

          {notification.created_at ? (
            <Text
              style={{
                fontSize: typography.caption.fontSize,
                fontWeight: typography.caption.fontWeight,
                lineHeight: typography.caption.lineHeight,
                color: colors.textMuted,
                marginLeft: spacing.sm,
              }}
            >
              {timeAgo(notification.created_at)}
            </Text>
          ) : null}
        </View>

        <Text
          style={{
            fontSize: typography.bodySmall.fontSize,
            fontWeight: typography.bodySmall.fontWeight,
            lineHeight: typography.bodySmall.lineHeight,
            color: colors.textSecondary,
          }}
          numberOfLines={2}
        >
          {notification.body}
        </Text>
      </View>
    </TouchableOpacity>
  );
}
