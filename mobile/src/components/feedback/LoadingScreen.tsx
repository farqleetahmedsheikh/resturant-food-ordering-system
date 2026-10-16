import { StyleSheet, View } from 'react-native';

import { AppText } from '@/src/components/common/AppText';
import { BrandLoadingMark, LoadingDots } from '@/src/components/feedback/BrandLoadingMark';
import { colors, spacing } from '@/src/theme';

export function LoadingScreen({ label = 'Loading...' }: { label?: string }) {
  return (
    <View style={styles.wrapper}>
      <View style={styles.content} accessibilityLiveRegion="polite">
        <BrandLoadingMark />
        <View style={styles.copy}>
          <AppText variant="caption" color={colors.brand.primary}>
            ARCADE KEBAB HOUSE
          </AppText>
          <AppText variant="title" style={styles.title}>Good food is on its way</AppText>
          <AppText color={colors.text.secondary} style={styles.label}>{label}</AppText>
        </View>
        <LoadingDots />
      </View>
      <AppText variant="caption" color={colors.text.muted}>
        FRESHLY MADE · READY FOR YOU
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing['4xl'],
    backgroundColor: colors.surface.page,
    padding: spacing['2xl'],
  },
  content: {
    alignItems: 'center',
    gap: spacing.xl,
  },
  copy: {
    alignItems: 'center',
    gap: spacing.sm,
  },
  title: {
    textAlign: 'center',
  },
  label: {
    textAlign: 'center',
  },
});
