import { PropsWithChildren } from 'react';
import { StyleSheet, View, ViewProps } from 'react-native';

import { radius, shadows, spacing, useTheme } from '@/src/theme';

export function AppCard({ children, style, ...props }: PropsWithChildren<ViewProps>) {
  const { colors } = useTheme();
  return (
    <View {...props} style={[styles.card, { borderColor: colors.border.light, backgroundColor: colors.surface.card }, style]}>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: radius.xl,
    borderWidth: 1,
    padding: spacing.lg,
    ...shadows.card,
  },
});
