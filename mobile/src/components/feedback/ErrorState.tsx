import { StyleSheet, View } from 'react-native';

import { AppButton } from '@/src/components/common/AppButton';
import { AppText } from '@/src/components/common/AppText';
import { colors, spacing } from '@/src/theme';

export function ErrorState({
  message,
  onRetry,
}: {
  message: string;
  onRetry?: () => void;
}) {
  return (
    <View style={styles.wrapper}>
      <AppText variant="title">Something needs attention</AppText>
      <AppText color={colors.text.secondary} style={styles.center}>
        {message}
      </AppText>
      {onRetry ? <AppButton label="Try Again" variant="secondary" onPress={onRetry} /> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    alignItems: 'center',
    gap: spacing.md,
    padding: spacing['2xl'],
  },
  center: {
    textAlign: 'center',
  },
});
