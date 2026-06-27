import { ActivityIndicator, StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, spacing } from '@/src/theme';

export function LoadingScreen({ label = 'Loading...' }: { label?: string }) {
  return (
    <View style={styles.wrapper}>
      <ActivityIndicator color={colors.brand.primary} size="large" />
      <AppText color={colors.text.secondary}>{label}</AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.lg,
    backgroundColor: colors.surface.page,
    padding: spacing.lg,
  },
});
