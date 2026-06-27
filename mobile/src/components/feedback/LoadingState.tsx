import { ActivityIndicator, StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { colors, spacing } from '@/src/theme';

type LoadingStateProps = {
  label?: string;
};

export function LoadingState({ label = 'Loading...' }: LoadingStateProps) {
  return (
    <View style={styles.wrapper}>
      <ActivityIndicator color={colors.brand.primary} />
      <AppText color={colors.text.secondary}>{label}</AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    minHeight: 160,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.md,
  },
});
