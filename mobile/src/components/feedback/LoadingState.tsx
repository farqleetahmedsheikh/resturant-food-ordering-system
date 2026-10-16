import { StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { LoadingDots } from '@/src/components/feedback/BrandLoadingMark';
import { colors, spacing } from '@/src/theme';

type LoadingStateProps = {
  label?: string;
};

export function LoadingState({ label = 'Loading...' }: LoadingStateProps) {
  return (
    <View style={styles.wrapper} accessibilityLiveRegion="polite">
      <LoadingDots />
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
