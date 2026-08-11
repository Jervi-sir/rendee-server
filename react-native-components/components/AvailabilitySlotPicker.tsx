import React, { useState } from 'react';
import { View, Text, TouchableOpacity, ScrollView } from 'react-native';
import { useTheme } from '../ThemeProvider';
import type { ProfessionalSchedule, CenterWorkingHours } from '../types';

export interface AvailabilitySlotPickerProps {
  slots: ProfessionalSchedule[] | CenterWorkingHours[];
  onSelectSlot?: (slot: ProfessionalSchedule | CenterWorkingHours) => void;
}

function isProfessionalSchedule(
  slot: ProfessionalSchedule | CenterWorkingHours,
): slot is ProfessionalSchedule {
  return 'day_of_week' in slot;
}

const dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function slotLabel(slot: ProfessionalSchedule | CenterWorkingHours): string {
  if (isProfessionalSchedule(slot)) {
    return dayLabels[slot.day_of_week] ?? '';
  }
  const d = new Date(slot.slot_date + 'T00:00:00');
  return d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });
}

function slotTimeRange(slot: ProfessionalSchedule | CenterWorkingHours): string {
  const start = slot.start_time ?? '--:--';
  const end = slot.end_time ?? '--:--';
  return `${start.slice(0, 5)} – ${end.slice(0, 5)}`;
}

function isSlotActive(slot: ProfessionalSchedule | CenterWorkingHours): boolean {
  if (isProfessionalSchedule(slot)) return slot.is_active;
  return slot.is_available;
}

export function AvailabilitySlotPicker({ slots, onSelectSlot }: AvailabilitySlotPickerProps) {
  const { colors, spacing, typography, radii } = useTheme();
  const [selectedId, setSelectedId] = useState<number | null>(null);

  const activeSlots = slots.filter(isSlotActive);

  if (activeSlots.length === 0) {
    return (
      <View style={{ padding: spacing.xl, alignItems: 'center' }}>
        <Text
          style={{
            fontSize: typography.body.fontSize,
            fontWeight: typography.body.fontWeight,
            lineHeight: typography.body.lineHeight,
            color: colors.textMuted,
          }}
        >
          No available slots
        </Text>
      </View>
    );
  }

  return (
    <View>
      <Text
        style={{
          fontSize: typography.label.fontSize,
          fontWeight: typography.label.fontWeight,
          lineHeight: typography.label.lineHeight,
          color: colors.textSecondary,
          marginBottom: spacing.sm,
        }}
      >
        Available Slots
      </Text>

      <ScrollView horizontal showsHorizontalScrollIndicator={false}>
        {activeSlots.map((slot) => {
          const isSelected = selectedId === slot.id;

          return (
            <TouchableOpacity
              key={slot.id}
              activeOpacity={0.7}
              onPress={() => {
                setSelectedId(slot.id);
                onSelectSlot?.(slot);
              }}
              style={{
                backgroundColor: isSelected ? colors.accent : colors.surface,
                borderWidth: 1,
                borderColor: isSelected ? colors.accent : colors.border,
                borderRadius: radii.md,
                paddingVertical: spacing.sm,
                paddingHorizontal: spacing.md,
                marginRight: spacing.sm,
                alignItems: 'center',
                minWidth: 80,
              }}
            >
              <Text
                style={{
                  fontSize: typography.label.fontSize,
                  fontWeight: '600',
                  lineHeight: typography.label.lineHeight,
                  color: isSelected ? '#FFFFFF' : colors.textPrimary,
                  marginBottom: 2,
                }}
              >
                {slotLabel(slot)}
              </Text>

              <Text
                style={{
                  fontSize: typography.caption.fontSize,
                  fontWeight: typography.caption.fontWeight,
                  lineHeight: typography.caption.lineHeight,
                  color: isSelected ? 'rgba(255,255,255,0.8)' : colors.textMuted,
                }}
              >
                {slotTimeRange(slot)}
              </Text>
            </TouchableOpacity>
          );
        })}
      </ScrollView>
    </View>
  );
}
