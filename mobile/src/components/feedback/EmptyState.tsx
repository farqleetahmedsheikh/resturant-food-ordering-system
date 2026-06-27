import { StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, radius, spacing } from '@/src/theme';

export function EmptyState({ title, message }: { title: string; message: string }) {
  return (
    <View style={styles.wrapper}>
      <View style={styles.mark} />
      <AppText variant="title" style={styles.center}>
        {title}
      </AppText>
      <AppText color={colors.text.secondary} style={styles.center}>
        {message}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    alignItems: 'center',
    gap: spacing.md,
    padding: spacing['2xl'],
  },
  mark: {
    height: 48,
    width: 48,
    borderRadius: radius.pill,
    backgroundColor: colors.brand.soft,
  },
  center: {
    textAlign: 'center',
  },
});
