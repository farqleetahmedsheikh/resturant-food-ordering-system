import { Pressable, StyleSheet, View } from 'react-native';

import { AppText } from './AppText';
import { colors, radius, spacing } from '@/src/theme';

type QuantityStepperProps = {
  value: number;
  onChange: (value: number) => void;
  min?: number;
  max?: number;
  disabled?: boolean;
  size?: 'default' | 'compact';
};

export function QuantityStepper({
  value,
  onChange,
  min = 1,
  max = 99,
  disabled = false,
  size = 'default',
}: QuantityStepperProps) {
  const canDecrease = !disabled && value > min;
  const canIncrease = !disabled && value < max;
  const isCompact = size === 'compact';

  return (
    <View style={[styles.wrapper, isCompact && styles.compactWrapper]}>
      <Pressable
        accessibilityRole="button"
        accessibilityLabel="Decrease quantity"
        disabled={!canDecrease}
        onPress={() => onChange(Math.max(min, value - 1))}
        style={[styles.control, isCompact && styles.compactControl, !canDecrease && styles.disabled]}
      >
        <AppText variant={isCompact ? 'body' : 'title'} color={colors.brand.primary}>
          -
        </AppText>
      </Pressable>
      <View style={[styles.value, isCompact && styles.compactValue]}>
        <AppText variant={isCompact ? 'body' : 'title'}>{value}</AppText>
      </View>
      <Pressable
        accessibilityRole="button"
        accessibilityLabel="Increase quantity"
        disabled={!canIncrease}
        onPress={() => onChange(Math.min(max, value + 1))}
        style={[styles.control, isCompact && styles.compactControl, !canIncrease && styles.disabled]}
      >
        <AppText variant={isCompact ? 'body' : 'title'} color={colors.brand.primary}>
          +
        </AppText>
      </Pressable>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    minHeight: 48,
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    overflow: 'hidden',
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border.strong,
    backgroundColor: colors.surface.card,
  },
  compactWrapper: {
    minHeight: 36,
    borderRadius: radius.md,
  },
  control: {
    minHeight: 48,
    minWidth: 48,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.brand.soft,
  },
  compactControl: {
    minHeight: 36,
    minWidth: 36,
  },
  value: {
    minWidth: 52,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.md,
  },
  compactValue: {
    minWidth: 34,
    paddingHorizontal: spacing.sm,
  },
  disabled: {
    opacity: 0.45,
  },
});
