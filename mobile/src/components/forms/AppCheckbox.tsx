import { Pressable, StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, radius, spacing } from '@/src/theme';

type AppCheckboxProps = {
  checked: boolean;
  label: string;
  onChange: (checked: boolean) => void;
};

export function AppCheckbox({ checked, label, onChange }: AppCheckboxProps) {
  return (
    <Pressable
      accessibilityRole="checkbox"
      accessibilityState={{ checked }}
      onPress={() => onChange(!checked)}
      style={styles.row}
    >
      <View style={[styles.box, checked && styles.boxChecked]}>
        {checked ? <View style={styles.dot} /> : null}
      </View>
      <AppText style={styles.label}>{label}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  row: {
    minHeight: 48,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  box: {
    height: 24,
    width: 24,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: radius.sm,
    borderWidth: 2,
    borderColor: colors.border.strong,
    backgroundColor: colors.surface.card,
  },
  boxChecked: {
    borderColor: colors.brand.primary,
    backgroundColor: colors.brand.primary,
  },
  dot: {
    height: 8,
    width: 8,
    borderRadius: radius.pill,
    backgroundColor: colors.text.inverse,
  },
  label: {
    flex: 1,
  },
});
